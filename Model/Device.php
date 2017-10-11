<?php


namespace Model;


use Utils\Database;
use Utils\QueryBuilder;

class Device extends ActiveRecord
{
    /**
     * @var string
     */
    protected $mac;

    /**
     * @var string
     */
    protected $ip;

    /**
     * @var string
     */
    protected $access_token;

    /**
     * @var int
     */
    protected $tariff_plan_id=null;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $image_version;

    /**
     * @var string
     */
    protected $last_active;

    /**
     * @var string
     */
    protected $keep_alive;

    /**
     * @var string
     */
    protected $stb_type;

    /**
     * @var string
     * this is account number
     */
    protected $ls;

    /**
     * @var string
     * this is full customer name
     */
    protected $fname;

    /**
     * @var bool
     * 1 - disabled, 0 -enabled;
     */
    protected $status=0;


    protected $_table='users';

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMac()
    {
        return $this->mac;
    }

    /**
     * @param string $mac
     */
    public function setMac($mac)
    {
        $this->mac = $mac;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @param string $access_token
     */
    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * @return int
     */
    public function getTariffPlanId()
    {
        return (int)$this->tariff_plan_id;
    }

    public function setTariffPlanId($tariff_id){
        $this->tariff_plan_id = $tariff_id;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getImageVersion()
    {
        return $this->image_version;
    }

    /**
     * @param string $image_version
     */
    public function setImageVersion($image_version)
    {
        $this->image_version = $image_version;
    }

    /**
     * @return \DateTime
     */
    public function getLastActive()
    {
        return new \DateTime($this->last_active);
    }

    /**
     * @param \DateTime $last_active
     */
    public function setLastActive(\DateTime $last_active)
    {
        $this->last_active = $last_active->format('Y-m-d H:m:s');
    }

    /**
     * @return string
     */
    public function getKeepAlive()
    {
        return new \DateTime($this->keep_alive);
    }

    public function setKeepAlive(\DateTime $keep_alive)
    {
        $this->keep_alive = $keep_alive->format('Y-m-d H:m:s');
    }




    /**
     * @return string
     */
    public function getStbType()
    {
        return $this->stb_type;
    }

    /**
     * @param string $stb_type
     */
    public function setStbType($stb_type)
    {
        $this->stb_type = $stb_type;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }


    /**
     * @param $password
     * @return bool
     */
    public function checkPassword($password){
        $check = md5(md5($password).$this->id);
        return $check == $this->password ? true:false;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function generateUniqueToken(){

        if(!$this->id){
            throw  new \Exception('Try to generate unique token to device without id');
        }
        $token = $this->id.'.'.md5(microtime(1));


        $query = QueryBuilder::query('SELECT * FROM access_tokens WHERE uid='.$this->id.' LIMIT 0,1');
        $token_record = $query->fetch_assoc();

        $data = array(
            'uid'     => $this->id,
            'token'   => $token,
            'refresh_token' => md5($token.''.uniqid()),
            'secret_key'    => md5($token.microtime(1)),
            'started' => date('Y-m-d H:i:s', time()),
            'expires' => date('Y-m-d H:i:s', time() + 3600)
        );

        if (empty($token_record)){

            $query = QueryBuilder::query(
                '
                 INSERT INTO access_tokens (uid,token,refresh_token,secret_key,started,expires) 
                 VALUES ('.$data['uid'].',\''.$data['token'].'\',\''.$data['refresh_token'].'\',\''.$data['secret_key'].'\',\''.$data['started'].'\',\''.$data['expires'].'\')
                ');

        }else{

            $query = QueryBuilder::query('
                 UPDATE access_tokens SET token=\''.$data['token'].'\', refresh_token=\''.$data['refresh_token'].'\', secret_key=\''.$data['secret_key'].'\', started=\''.$data['started'].'\',expires=\''.$data['expires'].'\'
                 WHERE uid='.$this->id
            );

        }
        if(!$query) throw  new \Exception(Database::getInstance()->getMysqli()->error,Database::getInstance()->getMysqli()->errno);

        $this->access_token=$token;

        return $token;
    }


    public function getAccount(){
        return $this->ls;
    }

    public function getFullname(){
        return $this->fname;
    }

    public function isEnabled(){
        return !(bool) $this->status;
    }

    /**
     * @param $status bool
     */
    public function setStatus($status){
        $this->status = (int)$status;
    }

    /**
     * @return array Channel[]
     */
    public function getChannels(){
        $channels = array();
        if(!$this->isEnabled()) return $channels;
        if(!$this->id) return $channels;

        if($this->tariff_plan_id>0){
            $sql = 'SELECT 
            itv.id
            FROM users AS u
            LEFT JOIN tariff_plan tp ON (tp.id=u.`tariff_plan_id`)
            LEFT JOIN package_in_plan pip ON (pip.plan_id=tp.id)
            LEFT JOIN service_in_package sip ON (sip.package_id=pip.package_id AND sip.type=\'tv\')
            LEFT JOIN itv  ON (itv.id=sip.service_id)
            WHERE u.id=
            '.$this->id;

            $result = QueryBuilder::query($sql);

            while ($r = $result->fetch_assoc()){
                array_push($channels,new Channel($r['id']));
            }
        }else{

        }


        return $channels;
    }

    /**
     * @return array
     *
     * !!! this code copypasted from stalker sources and adopted to database queries !!!
     */
    public function getBaseChannelsIds(){

        $array = array();
        $query = QueryBuilder::query('SELECT id FROM itv WHERE base_ch=1');

        while($row = $query->fetch_assoc()){
            array_push($array,$row['id']);
        }

        return $array;
    }

    /**
     * @return array|mixed
     *
     * !!! this code copypasted from stalker sources and adopted to database queries !!!
     */
    public function getBonusChannelsIds(){

        $query = QueryBuilder::query('SELECT bonus_ch FROM itv_subscription WHERE uid='.$this->id);
        if($query->num_rows==0) return array();
        $bonus_ch = $query->fetch_assoc();
        $bonus_ch_arr = unserialize($this->base64_decode($bonus_ch['bonus_ch']));

        if (!is_array($bonus_ch_arr)){
            return array();
        }

        return $bonus_ch_arr;
    }

    /**
     * @return array|mixed
     *
     * !!! this code copypasted from stalker sources and adopted to database queries !!!
     */
    public function getSubscriptionChannelsIds(){


        $query = QueryBuilder::query('SELECT * FROM moderators WHERE mac=\''.$this->mac.'\' AND status=1 LIMIT 0,1');

        if($query->num_rows==0){
            $return = array();
            $query = QueryBuilder::query('SELECT id FROM itv WHERE base_ch=0');
            while($row=$query->fetch_assoc()){
                array_push($return,$row['id']);
            }

            return $return;
        }

        $query = QueryBuilder::query('SELECT sub_ch FROM itv_subscription WHERE uid='.$this->id.' LIMIT 0,1');

        if($query->num_rows==0) return array();

        $sub_ch = $query->fetch_assoc();


        $sub_ch_arr = unserialize($this->base64_decode($sub_ch['sub_ch']));

        if (!is_array($sub_ch_arr)){
            return array();
        }

        return $sub_ch_arr;
    }


    public function getServicesByType(){

        if(!$this->tariff_plan_id) return array();

        $query = QueryBuilder::query('SELECT * FROM tariff_plan WHERE id='.$this->tariff_plan_id.' LIMIT 0,1');
        if($query->num_rows==0) return null;

        $plan = $query->fetch_assoc();

        $packages_ids = array();
        $query = QueryBuilder::query('SELECT package_id as id FROM package_in_plan WHERE plan_id='.$plan['id'].' AND optional=0');
        while($row = $query->fetch_assoc()){
            array_push($packages_ids,$row['id']);
        }

        $available_packages_ids = array();

        $query = QueryBuilder::query('SELECT package_id as id FROM package_in_plan WHERE plan_id='.$plan['id']);

        while($row = $query->fetch_assoc()){
            array_push($available_packages_ids,$row['id']);
        }

        $subscribed_packages_ids=array();

        $query = QueryBuilder::query('SELECT package_id FROM user_package_subscription WHERE user_id='.$this->id);

        while($row = $query->fetch_assoc()){
            array_push($subscribed_packages_ids,$row['package_id']);
        }

        $subscribed_packages_ids = array_filter($subscribed_packages_ids, function($package_id) use ($available_packages_ids){
            return in_array($package_id, $available_packages_ids);
        });

        if (!empty($subscribed_packages_ids)){
            $packages_ids = array_merge($packages_ids, $subscribed_packages_ids);
        }

        $packages_ids = array_unique($packages_ids);

        if (empty($packages_ids)){
            return null;
        }

        $packages = array();

        $sql = 'SELECT * FROM services_package WHERE type=\'tv\' AND id IN ('.implode(',',$packages_ids).')';
        $query = QueryBuilder::query($sql);

        while($row = $query->fetch_assoc()){
            array_push($packages,$row);
        }


        $contain_all_services = (bool) array_filter($packages, function($package){
            return $package['all_services'] == 1;
        });

        if ($contain_all_services){
            return 'all';
        }

        if (empty($packages)){
            return null;
        }

        $service_ids = array();

        foreach ($packages as $package){

            $ids = array();
            $query = QueryBuilder::query('SELECT service_id FROM service_in_package WHERE package_id='.$package['id']);
            while($row = $query->fetch_assoc()){
                array_push($ids,$row['service_id']);
            }
            $service_ids = array_merge($service_ids, $ids);
        }

        $service_ids = array_unique($service_ids);

        return $service_ids;
    }

    /**
     * @param $input
     * @return string
     * !!! this code copypasted from stalker sources !!!
     */
    private function base64_decode($input){

        return base64_decode(strtr($input, '-_,', '+/='));
    }



}