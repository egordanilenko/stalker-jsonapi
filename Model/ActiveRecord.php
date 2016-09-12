<?php


namespace Model;


use Exception\RecordNotFoundException;
use Utils\Database;
use Utils\Mysql;

abstract class ActiveRecord
{
    /**
     * @var int
     */
    protected $id;

    protected $_table;


    public function __construct($id=null)
    {
        if($id){
            //$fields = Mysql::getInstance()->select()->from($this->_table)->where('id',null,(int)$id)->get();

            $sql = 'SELECT * FROM '.$this->_table.' WHERE id = '.$id;
            $fields=Database::getInstance()->getMysqli()->query($sql)->fetch_assoc();
            if(count($fields)==0) throw  new RecordNotFoundException();
            foreach ($fields as $key=>$value){
                if(property_exists($this,$key)) $this->{$key}=$value;
            }
        }
    }

    public function save(){
        throw  new \Exception('Method not implemented');

    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }


}

