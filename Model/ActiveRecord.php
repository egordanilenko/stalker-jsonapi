<?php


namespace Model;


use Exception\RecordNotFoundException;
use Utils\Database;
use Utils\QueryBuilder;

class DatabaseField{
    public $name;
    public $value;
}


abstract class ActiveRecord
{
    /**
     * @var int
     */
    protected $id;

    private $_fieldMapping = array();
    protected $_table;

    private $_internalFields = array(
        '_table', 'id', '_fieldMapping', '_internalFields'
    );

    const INTEGER=0;
    const STRING=1;

    static private $typeMapping=array(
        'integer'=>self::INTEGER,
        'int'=>self::INTEGER,
        'bool'=>self::INTEGER,
        'string'=>self::STRING,
        'str'=>self::STRING
    );


    /**
     * fill field mapping
     */
    private function initFieldMapping(){
        $reflection = new \ReflectionClass($this);
        foreach ($reflection->getProperties() as $reflectionProperty) {

            if (!in_array($reflectionProperty->getName(), $this->_internalFields)) {
                $type = self::STRING;

                if (preg_match('/@var (\w+)/', $reflectionProperty->getDocComment(), $matches)) {
                    if (array_key_exists($matches[1], self::$typeMapping)) {
                        $type = self::$typeMapping[$matches[1]];
                    }
                }

                $this->_fieldMapping[$reflectionProperty->getName()]=$type;

            }
        }
    }

    /**
     * ActiveRecord constructor.
     * @param null $id
     * @throws RecordNotFoundException
     */
    public function __construct($id=null)
    {
        $this->initFieldMapping();
        if($id){
            $this->id = $id;
            $fields=QueryBuilder::query(QueryBuilder::select($this->_table, $id, array_keys($this->_fieldMapping)))->fetch_assoc();
            if(count($fields)==0) throw  new RecordNotFoundException();

            foreach ($fields as $key=>$value){
                if(isset($this->_fieldMapping[$key])) $value = $this->_fieldMapping[$key]==self::INTEGER? (int)$value:$value;
                if(property_exists($this,$key)) $this->{$key}=$value;
            }
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }


    /**
     * save entity to database
     */
    public function save(){
        $reflection = new \ReflectionClass($this);
        $databaseFields = array();

        foreach ($reflection->getProperties() as $reflectionProperty){

            if(!in_array($reflectionProperty->getName(), $this->_internalFields)){

                $type = $this->_fieldMapping[$reflectionProperty->getName()];
                $databaseField = new DatabaseField();
                $databaseField->name = $reflectionProperty->getName();
                $reflectionProperty->setAccessible(true);
                $databaseField->value = $type==self::STRING ? "'".$reflectionProperty->getValue($this)."'" : (int)$reflectionProperty->getValue($this);
                $reflectionProperty->setAccessible(false);

                array_push($databaseFields,$databaseField);
            }
        }

        $sql = $this->id ? QueryBuilder::update($this->id,$this->_table,$databaseFields) : QueryBuilder::insert($this->_table,$databaseFields);

        QueryBuilder::query($sql);
        if (!$this->id) $this->id=  $this->id=Database::getInstance()->getMysqli()->insert_id;
    }

}

