<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/13/17
 * Time: 1:39 AM
 */

namespace Charlotte\Core;


class SQL
{
    protected $_dbHandle;
    protected $_result;

    private $_table;

    // connect to db
    public function connect($host, $user, $pass, $dbname, $port){
        try {
            $dsn = sprintf("mysql:host=%s;dbname=%s;port=%s", $host, $dbname, $port);
            $this->_dbHandle = new \PDO($dsn, $user, $pass, array(\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC));
        } catch (\PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    public function useTable($table) {
        $this->_table = $table;
    }
    // select all
    public function selectAll(){
        $sql = sprintf("select * from `%s`", $this->_table);
        $sth = $this->_dbHandle->prepare($sql);
        $sth->execute();
        return $sth->fetchAll();
    }
    // search by id or ...
    public function select($id){
        $sql = sprintf("select * from `%s` where `id` = '%s'", $this->_table, $id);
        $sth = $this->_dbHandle->prepare($sql);
        $sth->execute();

        return $sth->fetch();
    }
    // delete by id or ...
    public function delete($id){
        $sql = sprintf("delete from `%s` where `id` = '%s'", $this->_table, $id);
        $sth = $this->_dbHandle->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
    // custom search
    public function query($sql){
        $sth = $this->_dbHandle->prepare($sql);
        $sth->execute();
        return $sth->rowCount();
    }
    // insert record
    public function add($data){
        $sql = sprintf("insert into `%s` %s", $this->_table, $this->formatInsert($data));
        return $this->query($sql);
    }
    // update record
    public function update($id, $data){
        $sql = sprintf("update `%s` set %s where `id` = '%s'", $this->_table, $this->formatUpdate($data), $id);
        return $this->query($sql);
    }
    // convert array into insert query
    private function formatInsert($data){
        $fields = array();
        $values = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("`%s`", $key);
            $values[] = sprintf("'%s'", $value);
        }
        $field = implode(',', $fields);
        $value = implode(',', $values);
        return sprintf("(%s) values (%s)", $field, $value);
    }
    // convert array into update query
    private function formatUpdate($data){
        $fields = array();
        foreach ($data as $key => $value) {
            $fields[] = sprintf("`%s` = '%s'", $key, $value);
        }
        return implode(',', $fields);
    }
}