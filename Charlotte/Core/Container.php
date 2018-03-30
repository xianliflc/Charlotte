<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/13/17
 * Time: 1:41 AM
 */

namespace Charlotte\Core;

use Charlotte\Core\SQL;

class Container extends SQL
{
    const version = 'V2';
    protected $_table;

    function __construct($config, $table)
    {
        if (isset($table)) {
            $this->_table = $table;
        }
        $this->config = $config;

        if (!isset($this->config['db'])) {
            throw new \Exception('No config found for database connection', 500);
        }
        $db = $this->config['db'];

        if (!isset($db['host']) || !isset($db['user']) || !isset( $db['password']) || !isset( $db['default_db'])) {
            throw new \Exception('Invalid config for database connection, please check config', 500);
        }

        // connect to db
        $this->connect($db['host'], $db['user'], $db['password'], $db['default_db'], isset($db['port'])? $db['port'] : '3306');

    }

    public function queryV2($sql){
        $sth = $this->_dbHandle->prepare($sql);
        $sth->execute();
        return $sth->fetchAll();
    }

    public function query($sql) {

        return $this->{'query' . self::version}($sql);
    }

    protected function buildQuery($params) {
        //TODO: add logic to use query object
        return false;
    }

    protected function run($params) {
        //TODO: run queries or query all at once
        return false;
    }

    function __destruct()
    {
        
    }
}