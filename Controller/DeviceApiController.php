<?php

namespace Controller;


use Exception\DeviceApiIncorrectCredintialsExcption;
use Exception\DeviceApiNotFoundException;
use Exception\DeviceApiRegistrationRequiredException;
use Exception\DeviceApiWrongSyntaxException;
use Model\AgeGroup;
use Model\CasConfig;
use Model\Channel;
use Model\Command;
use Model\Device;
use Model\EpgItem;
use Model\FavoriteGroup;
use Model\Request;
use Model\vod\VodTag;
use Response\AuthResponse;
use Response\ChannelsResponse;
use Response\EpgResponse;
use Response\MessagesResponse;
use Response\RegisterResponse;
use Response\RequestResponse;
use Response\M3u8Response;
use Response\ServerInfoResponse;
use Response\PreflightResponse;
use Response\ShortEpgResponse;
use Response\UnregisterResponse;
use Response\UserInfoResponse;
use Response\vod\VodTagListResponse;
use Type\PollType;
use Type\ShortEpgType;
use Utils\PoTranslator;
use Utils\QueryBuilder;


class DeviceApiController extends AbstractController
{
    const REGISTER_TYPE='login';
    const SESSION_TYPE='token';
    
    /**
     * @var array
     */
    private static $anonymousAction = array(
        'Controller\DeviceApiController@serverInfoAction',
        'Controller\DeviceApiController@postRegisterAction',
        'Controller\DeviceApiController@authAction',
        'Controller\DeviceApiController@shortEpgAction',
        'Controller\DVRController@timeShiftAction'
    );

    /**
     * @var Channel[]
     */
    private $channels = array();

    /**
     * @var FavoriteGroup[]
     */
    private $favorites = array();

    /**
     * CORS preflight request (request with OPTIONS method to target url, then original request), must return valid response with correct headers
     * @link https://en.wikipedia.org/wiki/Cross-origin_resource_sharing
     * @return JsonResponse
     */
    public function preflightAction(){
        return new RequestResponse('preflight', 200, new PreflightResponse());
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#server_info
     * @return JsonResponse
     */
    public function serverInfoAction()
    {
        $serverInfoResponse = new ServerInfoResponse(
            self::API_VERSION, //protocol version
            '', // Stalker not provide ISP name
            time(), //server time
            true, //auth enabled
            60*60*3,//channel list update interval $this->coreService->getConfig()->get('channel_list_update_interval'),
            $this->clientIp,
            $this->getPoll(),
            array() //cas types
        );

        return new RequestResponse('server_info',200,$serverInfoResponse);
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#authentication
     */
    public function authAction()
    {
        if(!$this->device->getId() && !$this->authUrl){

            $this->registered = true;
            $this->device->setLastActive(new \DateTime());

            $this->device->save();
            $this->device->generateUniqueToken();
            if($this->getSafe('default_stb_status',0)==0){
                $this->device->setStatus(false); // inversed status, true = off, false = on
            }else{
                $this->device->setStatus(true);
            }

            $query = QueryBuilder::query('SELECT id FROM tariff_plan WHERE user_default=1 LIMIT 0,1');
            if($query->num_rows>0){
                $result = $query->fetch_assoc();
                $this->device->setTariffPlanId($result['id']);
            }
            $this->device->save();
        }


        if(!$this->device->getAccessToken()) throw  new DeviceApiRegistrationRequiredException('Need registration');

        $authResponse = new AuthResponse(
            $this->registered,
            self::REGISTER_TYPE,
            self::SESSION_TYPE,
            $this->device->getAccessToken()
        );
        $code = 200;


        return new RequestResponse('auth',$code,$authResponse);
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#registration
     * @return JsonResponse
     * @throws DeviceApiIncorrectCredintialsExcption
     * @throws DeviceApiWrongSyntaxException
     */
    public function postRegisterAction()
    {

        $registerResponse = new RegisterResponse(null);
        $content = $this->request->getContent();
        $json= json_decode($content);
        if($json && strlen($content)>0){
            if(!property_exists($json,'login') || !property_exists($json,'password')) throw  new DeviceApiIncorrectCredintialsExcption('login and password fields not found');
            $login = $json->login;
            $password = $json->password;

            if($this->device->getId() && $this->device->getLogin() == $login && $this->device->checkPassword($password)){
                $this->registered=true;

                $this->device->generateUniqueToken();
                $this->device->save();

                $registerResponse->token=$this->device->getAccessToken();
            }else{
                $sql = 'SELECT id FROM users WHERE login = \''.$login.'\' AND password = MD5(CONCAT(\''.md5($password).'\',id)) AND mac =\'\' LIMIT 0,1';

                $query = QueryBuilder::query($sql);
                if($query->num_rows==1){
                    $this->registered=true;
                    $result = $query->fetch_assoc();
                    $mac = $this->device->getMac();
                    if($this->device->getId()){
                        $this->device->setMac(null);
                        $this->device->save();
                    }
                    $this->device= new Device($result['id']);
                    $this->device->setMac($mac);
                    $this->device->save();
                    $this->device->generateUniqueToken();
                    $this->device->save();
                    $registerResponse->token = $this->device->getAccessToken();
                } else{
                    throw new DeviceApiIncorrectCredintialsExcption('Login or password is incorrect');
                }

            }
        }else{
            throw new DeviceApiIncorrectCredintialsExcption('Login or password is incorrect');
        }

        return new RequestResponse('register',200,$registerResponse);
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#cancel_registration
     * @return JsonResponse
     * @throws DeviceApiRegistrationRequiredException
     */
    public function unregisterAction()
    {
        if(!$this->registered) throw new DeviceApiRegistrationRequiredException('Registration requied');
        $this->device->setMac(null);
        $this->device->setAccessToken(null);
        $this->device->save();
        return new RequestResponse('unregister',200,new UnregisterResponse());
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#user_info
     * @return JsonResponse
     * @throws DeviceApiRegistrationRequiredException
     */
    public function userInfoAction(){
        if(!$this->registered) throw new DeviceApiRegistrationRequiredException('Registration required');

        $userInfoResponse = new UserInfoResponse(
            $this->device->getFullname(),
            $this->device->getAccount(),
            $this->device->isEnabled(),
            0,
            0
        );

        return new RequestResponse('user_info',200,$userInfoResponse);

    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#channel_list
     * @return JsonResponse
     */
    public function channelsAction()
    {
        $baseLogoUrl = isset($this->config['stalker_host']) ? 'http://'.$this->config['stalker_host']: 'http://'.$_SERVER['SERVER_NAME'];
        $baseLogoUrl = $baseLogoUrl.'/stalker_portal/misc/logos/240/';
        $channelsResponse = new ChannelsResponse(
            0,//channel version, TODO: check channel version change
            0,//epg version
            $this->getChannels(),
            $this->ageGroups,
            $this->getFavorites(),
            '', //channels hash
            new CasConfig(),
            $baseLogoUrl

        );

        return new RequestResponse('channels',200,$channelsResponse);
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#epg_download
     * @return JsonResponse
     * @throws DeviceApiNotFoundException
     */
    public function epgAction($channelId, $date)
    {
        $date = str_replace(".json","",$date);

        /**
         * @var $channel Channel
         */
        $channel = new Channel($channelId);

        if(!$channel){
            throw new DeviceApiNotFoundException('Channel with id'.$channelId.' not found');
        }
        $startDateTime = new \DateTime($date);
        $startDateTime->setTime(0,0,0);
        $endDateTime = clone ($startDateTime);
        $endDateTime->setTime(23,59,59);

        $sql = 'SELECT id FROM epg WHERE ch_id='.$channel->getId().' AND time >= \''.$startDateTime->format('c').'\' AND time <= \''.$endDateTime->format('c').'\'';
        $result = QueryBuilder::query($sql);
        $events = array();
        while($row = $result->fetch_assoc()){
            array_push($events, new EpgItem($row['id']));
        }

        $epgResponse = new EpgResponse(
            0, //EPG version
            $channelId,
            $startDateTime,
            $events,
            $this->ageGroups
        );


        if(count($epgResponse->events)==0) throw new DeviceApiNotFoundException('Events not found');
        return new RequestResponse('epg',200,$epgResponse);
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#short_epg_download
     * @return JsonResponse
     */
    public function shortEpgAction(){
        $channels = array();
        foreach($this->getChannels() as $channel){
            array_push($channels,new ShortEpgType($channel));
        }
        $response = new ShortEpgResponse(1,$channels,array());
        return new RequestResponse('short_epg',200,$response);

    }


    public function channelShortEpgAction($channelId){

        /**
         * @var $channel Channel
         */

        $channel = new Channel($channelId);
        if(!$channel){
            throw new DeviceApiNotFoundException('Channel with id'.$channelId.' not found');
        }
        $channels = array(new ShortEpgType($channel));
        $response = new ShortEpgResponse(1,$channels,$this->ageGroups);
        return new RequestResponse('short_epg',200,$response);

    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#messages
     * @return JsonResponse
     */
    public function messagesAction()
    {
        /**
         * @var $commands Command[]
         */
        $commands = array();

        $query = QueryBuilder::query('SELECT id FROM events WHERE uid='.$this->device->getId().' AND ended=0 AND sended=0');

        if($query->num_rows>0){
            while($row = $query->fetch_assoc()){
                array_push($commands, new Command($row['id']));
            }
        }
        $messagesResponse = new MessagesResponse($this->getPoll(),$commands);
        foreach ($commands as $command){
            $command->setSended(1);
            $command->setEnded(1);
            $command->save();
        }
        return new RequestResponse('messages',200,$messagesResponse);
    }


    public function postMessagesAction(){

        $messagesResponse = new MessagesResponse($this->getPoll(),array());
        return new RequestResponse('messages',200,$messagesResponse);
    }
    
    /**
     * @return PollType
     */
    private function getPoll(){
        $poll =  new PollType();
        $poll->interval = 60;
        $r = hexdec(str_replace(':','',$this->device->getMac())) % 100;
        if($r==0) $r=100;
        $poll->timeslot = $r*0.01;

        return $poll;
    }

    /**
     * @return string hash of available channels
     */
/*    private function getChannelsHash(){
        $ids = array();
        foreach($this->getChannels() as $channel){
            array_push($ids,$channel->getId());
        }
        sort($ids);
        return md5(implode(';', $ids));
    }*/

    /**
     * Return response with all needed headers
     * @param RequestResponse $requestResponse
     * @param int $code
     * @return JsonResponse
     */
    

    private function getFavorites(){
        if(count($this->favorites)==0){
            $result = QueryBuilder::query('SELECT id FROM tv_genre');

            while ($row = $result->fetch_assoc()){
                array_push($this->favorites, new FavoriteGroup($row['id']));
            }

        }
        return $this->favorites;
    }

    private function getChannels(){

        if(count($this->channels)==0 && $this->device->isEnabled()){
            foreach ($this->getChannelsIds() as $id){
                array_push($this->channels, new Channel($id));
            }
        }
        return $this->channels;

    }

   


    public function getChannelsIds() {

        //если включены тарифные планы и выключена подписка тв на тарифных планах
        if ($this->getSafe('enable_tariff_plans', false) && !$this->getSafe('enable_tv_subscription_for_tariff_plans', false)){

            $subscription = $this->device->getServicesByType();
            if (empty($subscription)){
                $subscription = array();
            }
            $channel_ids = $subscription;
        }else{
            $channel_ids = array_unique(
                array_merge(
                    $this->device->getSubscriptionChannelsIds(),
                    $this->device->getBonusChannelsIds(),
                    $this->device->getBaseChannelsIds()
                )
            );
        }


        if($channel_ids == 'all'){
            $channel_ids=array();
            $query = QueryBuilder::query('SELECT id FROM itv');
            while($row=$query->fetch_assoc()){
                array_push($channel_ids,$row['id']);
            }

        }

        return $channel_ids;
    }



    public function isRegistered(){
        return $this->registered;
    }

    /**
     * @return array
     */
    public static function getAnonymousAction()
    {
        return self::$anonymousAction;
    }


}
