<?php


namespace Utils;


use Model\DatabaseField;

class QueryBuilder{


    public static function select($table,$id=null,array $fields = array()){
        $sql = 'SELECT ';
        $sql.= count($fields)==0?'*':implode(',',$fields);
        $sql.=' FROM '.$table;
        $sql.= $id==null ? '' : ' WHERE id='.$id;
        return $sql;

    }
    /**
     * @param $table string
     * @param array | DatabaseField[] $databaseFields
     * @return string
     */
    public static function insert($table, array $databaseFields){
        $fields = array();
        $values = array();
        foreach ($databaseFields as $databaseField){
            array_push($fields,$databaseField->name);
            array_push($values,$databaseField->value);
        }
        $sql = 'INSERT INTO '.$table.' ('.implode(',',$fields).') VALUES ('.implode(',',$values).')';
        return $sql;
    }

    /**
     * @param $id int
     * @param $table string
     * @param array | DatabaseField[] $databaseFields
     * @return string
     */
    public static function update($id, $table, array $databaseFields){
        $sql = 'UPDATE '.$table.' SET ';
        foreach ($databaseFields as $databaseField){
            $sql.= $databaseField->name.' = '.$databaseField->value.',';
        }
        $sql = trim($sql, ',');
        $sql.=' WHERE id='.(int)$id;

        return $sql;
    }

    public static function query($sql){
        $result = Database::getInstance()->getMysqli()->query($sql);
        if(is_bool($result)){
            if(!$result) throw new \Exception('SQL error on: '.$sql);
        }
        return $result;
    }
}
