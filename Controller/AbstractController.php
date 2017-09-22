<?php
namespace Controller;

use Exception\DeviceApiWrongSyntaxException;
use Model\AgeGroup;
use Model\Device;
use Model\Request;
use Utils\PoTranslator;
use Utils\QueryBuilder;


abstract class AbstractController {

    const API_VERSION = 1;

    /**
     * @var Device
     */
    protected $device;
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var integer
     */
    protected $clientIp;



    protected $registered = false;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var AgeGroup[]
     */
    protected $ageGroups = array();

    /**
     * @var array
     */
    protected $config = array();

    protected $debug=false;

    protected $authUrl = null;


    public function __construct(Request $request, array $config)
    {

        $this->config = $config;
        $this->request = $request;
        //var_dump($request);
        $this->authUrl = $this->getSafe('auth_url',null);
        $this->debug = $this->getSafe('debug',false);

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

            $query = QueryBuilder::query("SELECT id FROM users WHERE mac LIKE '$mac' LIMIT 0,1");
            $search = $query->fetch_object();

            $id = is_object($search) ? (int)$search->id:null;
            $this->device = new Device($id);
            $this->device->setKeepAlive(new \DateTime());
            $this->device->setImageVersion($request->getHeaderParam('device-firmware'));
            $this->device->setStbType($request->getHeaderParam('device-type'));
            $this->device->setLocale($lang);
            $this->device->setIp($this->clientIp);
            $this->device->setMac($mac);

            if($token!=null && $this->device->getAccessToken() == $token) $this->registered = true;


            if($this->device->getId()) $this->device->save();
        }else{
            throw  new DeviceApiWrongSyntaxException('Mac address not present');
        }
    }

    public function getSafe($key, $default) {

        return isset($this->config[$key]) ? $this->config[$key]: $default;

    }

    public function getParseUrl(){
        return parse_url($this->request->getPath());
    }

    public function getUrlParam() {
        //var_dump($this->getParseUrl());
        if(array_key_exists('query',$this->getParseUrl())) {
            parse_str($this->getParseUrl()["query"],$output);
            return $output;
        }

        return [];
    }
}