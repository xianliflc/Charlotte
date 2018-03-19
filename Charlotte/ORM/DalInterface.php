<?php
namespace Charlotte\ORM;

Interface DalInterface {

    public function connect($host, $user, $pass, $dbname, $port, $driver);

    public function close();

    public function query(string $sql);

}