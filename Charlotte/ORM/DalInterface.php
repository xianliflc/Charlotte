<?php
namespace Charlotte\ORM;

Interface DalInterface {

    public function connect();

    public function close();

    public function query(string $sql);

}