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
use Response\AuthResponse;
use Response\ChannelsResponse;
use Response\EpgResponse;
use Response\MessagesResponse;
use Response\RegisterResponse;
use Response\RequestResponse;
use Response\JsonResponse;
use Response\ServerInfoResponse;
use Response\PreflightResponse;
use Response\ShortEpgResponse;
use Response\UnregisterResponse;
use Response\UserInfoResponse;
use Type\PollType;
use Type\ShortEpgType;
use Utils\Database;
use Utils\PoTranslator;


class DeviceApiController
{
    const REGISTER_TYPE='login';
    const SESSION_TYPE='token';
    const API_VERSION = 1;

    /**
     * @var string
     */
    private $language;

    private $registered = false;

    /**
     * @var Device
     */
    private $device;
    /**
     * @var Request
     */
    private $request;

    /**
     * @var integer
     */
    private $clientIp;

    /**
     * @var array
     */
    private static $anonymousAction = array('serverInfoAction','registerAction','authAction');

    /**
     * @var Channel[]
     */
    private $channels = array();

    /**
     * @var FavoriteGroup[]
     */
    private $favorites = array();

    /**
     * @var AgeGroup[]
     */
    private $ageGroups = array();

    /**
     * @var array
     */
    private $config = array();

    /**
     * DeviceApiController constructor.
     * @param Request $request
     * @param array $config
     */
    public function __construct(Request $request, array $config)
    {

        $this->config = $config;
        $this->request = $request;

        //virtual Age Group
        array_push($this->ageGroups, new AgeGroup(0,18,'18+'));

        $this->clientIp = $request->getHeaderParam('x-real-ip') ? $request->getHeaderParam('x-real-ip') : $_SERVER['REMOTE_ADDR'];

        $mac = strtoupper($request->getHeaderParam('Mac-Address'));

        $lang = $this->request->getHeaderParam('Accept-Language');

        if($lang){
            if(strlen($lang)==2){
                $lang = strtolower($lang).'_'.strtoupper($lang).'.utf8';
            }else{
                $lang = str_replace('-','_',substr($lang,0,strpos($lang,','))).'.utf8';
            }

        }else{
            $lang=$this->getSafe('default_locale','ru_RU.utf8');
        }

        $this->language  = $lang;

        $path = $this->getSafe('stalker_path','/var/www/stalker_portal/').'/server/locale/'.substr($lang,0,2).'/LC_MESSAGES/stb.po';

        try{
            PoTranslator::getInstance()->setPath($path);
        }catch (\Exception $e){
            $path = $this->getSafe('stalker_path','/var/www/stalker_portal/').'/server/locale/'.substr($this->getSafe('default_locale','en'),0,2).'/LC_MESSAGES/stb.po';
            PoTranslator::getInstance()->setPath($path);
        }

        $token = $request->getHeaderParam('Auth-Token');

        if($mac){

            $query = Database::getInstance()->getMysqli()->query("SELECT id FROM users WHERE mac LIKE '$mac' LIMIT 0,1");
            $search = $query->fetch_object();

            $id = is_object($search) ? (int)$search->id:null;
            $this->device = new Device($id);
            $this->device->setLastActive(new \DateTime());
            $this->device->setImageVersion($request->getHeaderParam('device-firmware'));
            $this->device->setStbType($request->getHeaderParam('device-type'));
            $this->device->setLocale($lang);
            $this->device->setIp($this->clientIp);
            $this->device->setMac($mac);

            if($token!=null && $this->device->getAccessToken() == $token) $this->registered = true;

            if($this->device->getId()) $this->device->save();
        }


    }

    /**
     * CORS preflight request (request with OPTIONS method to target url, then original request), must return valid response with correct headers
     * @link https://en.wikipedia.org/wiki/Cross-origin_resource_sharing
     * @return JsonResponse
     */
    public function preflightAction(){
        return self::generateResponse(new RequestResponse('preflight', 200, new PreflightResponse()));
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#server_info
     * @return JsonResponse
     */
    public function serverInfoAction()
    {
        $serverInfoResponse = new ServerInfoResponse(
            self::API_VERSION,
            '', //TODO: fix it: Provider name via config or fetch from databse
            time(),
            true,
            60,//$this->coreService->getConfig()->get('channel_list_update_interval'),
            $this->clientIp,
            $this->getPoll(),
            array()
        );

        return self::generateResponse(new RequestResponse('server_info',200,$serverInfoResponse));
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#authentication
     */
    public function authAction()
    {
        if(!$this->device->getId()){

            $this->registered = true;

            $this->device->save();
            $this->device->generateUniqueToken();
            if($this->getSafe('default_stb_status',0)==0){
                $this->device->setStatus(false); // inversed status, true = off, false = on
            }else{
                $this->device->setStatus(true);
            }

            $query = Database::getInstance()->getMysqli()->query('SELECT id FROM tariff_plan WHERE user_default=1 LIMIT 0,1');
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

        return self::generateResponse(new RequestResponse('auth',$code,$authResponse));
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#registration
     * @return JsonResponse
     * @throws DeviceApiIncorrectCredintialsExcption
     * @throws DeviceApiWrongSyntaxException
     */
    public function registerAction()
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
                $query = Database::getInstance()->getMysqli()->query($sql);
                if($query->num_rows==1){
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
                    $registerResponse->token = $this->device->getAccessToken();
                } else{
                    throw new DeviceApiIncorrectCredintialsExcption('Login or password is incorrect');
                }

            }
        }else{
            throw new DeviceApiIncorrectCredintialsExcption('Login or password is incorrect');
        }

        return self::generateResponse(new RequestResponse('register',200,$registerResponse));
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
        return self::generateResponse(new RequestResponse('unregister',200,new UnregisterResponse()));
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

        return self::generateResponse(new RequestResponse('user_info',200,$userInfoResponse));

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
            0,//channel version
            0,//epg version
            $this->getChannels(),
            $this->ageGroups,
            $this->getFavorites(),
            '',
            new CasConfig(),
            $baseLogoUrl

        );

        $response = new RequestResponse('channels',200,$channelsResponse);
        return self::generateResponse($response,$response->status);
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#epg_download
     * @return JsonResponse
     * @throws DeviceApiNotFoundException
     */
    public function epgAction()
    {

        $date = $this->request->getGetParam('date');
        $channelId=(int)$this->request->getGetParam('channel_id');
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
        $result = Database::getInstance()->getMysqli()->query($sql);
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
        $response = new RequestResponse('epg',200,$epgResponse);
        return self::generateResponse($response,$response->status);
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#short_epg_download
     * @return JsonResponse
     * TODO: fixit
     */
    public function shortEpgAction(){
        $channels = array();
//        foreach($this->getChannels() as $channel){
//            array_push($channels,new ShortEpgType($channel));
//        }
        $response = new ShortEpgResponse(1,$channels,array());
        return self::generateResponse(new RequestResponse('short_epg',200,$response));

    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#short_epg_download
     * @param Request $request
     * @return JsonResponse
     * @throws DeviceApiNotFoundException
     * TODO: fixit
     */
    public function channelShortEpgAction(){
        $channelId=$this->request->getGetParam('channel');
        /**
         * @var $channel Channel
         */

        $channel = new Channel($channelId);
        if(!$channel){
            throw new DeviceApiNotFoundException('Channel with id'.$channelId.' not found');
        }
        $channels = array(new ShortEpgType($channel));
        $response = new ShortEpgResponse(1,$channels,array());
        return self::generateResponse(new RequestResponse('short_epg',200,$response));

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
        $commands = array(); //TODO: fixit

        $query = Database::getInstance()->getMysqli()->query('SELECT id FROM events WHERE uid='.$this->device->getId().' AND ended=0 AND sended=0');

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
        return self::generateResponse(new RequestResponse('messages',200,$messagesResponse));
    }

    /**
     * @link http://wiki.tvip.ru/private/json_api#messages
     * @param Request $request
     * @return JsonResponse
     * @throws DeviceApiNotFoundException
     * @throws DeviceApiWrongSyntaxException
     *
     */
    public function postMessagesAction(){


        $messagesResponse = new MessagesResponse($this->getPoll(),array());
        return self::generateResponse(new RequestResponse('messages',200,$messagesResponse));
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
    private function getChannelsHash(){
        $ids = array();
        foreach($this->getChannels() as $channel){
            array_push($ids,$channel->getId());
        }
        sort($ids);
        return md5(implode(';', $ids));
    }

    /**
     * Return response with all needed headers
     * @param RequestResponse $requestResponse
     * @param int $code
     * @return JsonResponse
     */
    public static function generateResponse(RequestResponse $requestResponse,$code=200){

        $headers = array(
            'Access-Control-Allow-Methods'     => 'GET, POST, OPTIONS',
            'Access-Control-Request-Headers'   => 'Accept, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Headers'     => 'Mac-Address, Device-Type, Device-Version, Device-Os, Device-Firmware, X-Auth-Token, Auth-Token'
        );

        if(isset($_SERVER['HTTP_ORIGIN'])){
            $headers = array_merge(
                $headers,
                array(
                    'Access-Control-Allow-Origin'=>$_SERVER['HTTP_ORIGIN']
                )
            );
        }

        return new JsonResponse($requestResponse,$code,$headers);
    }

    private function getFavorites(){
        if(count($this->favorites)==0){
            $result = Database::getInstance()->getMysqli()->query('SELECT id FROM tv_genre');

            while ($row = $result->fetch_assoc()){
                array_push($this->favorites, new FavoriteGroup($row['id']));
            }



        }
        return $this->favorites;
    }

    private function getChannels(){

        if(count($this->channels)==0){
            foreach ($this->getChannelsIds() as $id){
                array_push($this->channels, new Channel($id));
            }
        }
        return $this->channels;

    }

    public function getSafe($key, $default){

        return isset($this->config[$key]) ? $this->config[$key]: $default;

    }


    public function getChannelsIds(){

        //если включены тарифные планы и выключена подписка тв на тарифных планах
        if ($this->getSafe('enable_tariff_plans', false) && !$this->getSafe('enable_tv_subscription_for_tariff_plans', false)){

            $subscription = $this->device->getServicesByType('tv');
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
            $query = Database::getInstance()->getMysqli()->query('SELECT id FROM itv');
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
