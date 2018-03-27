<?php
namespace Charlotte\ORM;

Interface DalInterface {

    /**
     * @param $host
     * @param $user
     * @param $pass
     * @param $dbname
     * @param $port
     * @param $driver
     * @return mixed
     */
    public function connect($host, $user, $pass, $dbname, $port, $driver);

    /**
     * @return mixed
     */
    public function close();

    /**
     * @param string $sql
     * @return mixed
     */
    public function query(string $sql);

}