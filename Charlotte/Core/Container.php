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

    function __construct($table)
    {
        if (isset($table)) {
            $this->_table = $table;
        }
        // connect to db
        $this->connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

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
        return false;
    }

    protected function run($params) {
        return false;
    }

    function __destruct()
    {

    }
}