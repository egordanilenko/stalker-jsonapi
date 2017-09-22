<?php


namespace Model;


abstract class DvrServer
{

    protected $baseUrl;


    protected $variables = array(
        '%a',
        '%A',
        '%b',
        '%B',
        '%c',
        '%C',
        '%d',
        '%D',
        '%e',
        '%F',
        '%g',
        '%G',
        '%h',
        '%H',
        '%I',
        '%j',
        '%k',
        '%l',
        '%m',
        '%M',
        '%n',
        '%N',
        '%p',
        '%P',
        '%r',
        '%R',
        '%s',
        '%S',
        '%t',
        '%T',
        '%u',
        '%U',
        '%V',
        '%w',
        '%W',
        '%x',
        '%X',
        '%y',
        '%Y',
        '%z',
        '%Z',
    );


    protected $defaultTimeShiftDepthSeconds;

    abstract public function getDvrServerType();


    /**
     * @return mixed
     */
    public function getDefaultTimeShiftDepthSeconds()
    {
        return $this->defaultTimeShiftDepthSeconds;
    }



    public function getVariables(){
        return $this->variables;
    }

    public function getVariable($name){

        if(in_array($name,($this->variables))) return '${'.str_replace('%','',$name).'}';
        throw new \Exception('Unable to find '.$name.' format');
    }

    abstract public function getTimeshiftUrl($string);
}