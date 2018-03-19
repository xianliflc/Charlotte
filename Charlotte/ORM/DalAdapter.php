<?php

namespace Charlotte\ORM;

class DalAdapter implements DalInterface{

    private $handle;
    private $db;
    private $table;

    public function __construct(array $config = array()) {

        $this->connect(
            array_key_exists('host', $config)? $config['host'] : '',
            array_key_exists('user', $config)? $config['user'] : 'root',
            array_key_exists('password', $config)? $config['password'] : '',
            array_key_exists('default_db', $config)? $config['default_db'] : '',
            array_key_exists('port', $config)? $config['port'] : '3306',
            array_key_exists('driver', $config)? $config['driver'] : 'mysql'

        );
    }


    public function connect($host, $user, $pass, $dbname, $port = '3306', $driver = 'mysql') {
        try {
            $dsn = sprintf("%s:host=%s;dbname=%s;port=%s", $driver, $host, $dbname, $port);
            $this->handle = new \PDO($dsn, $user, $pass, array(\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, \PDO::ATTR_PERSISTENT => true));
            $this->handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->db = $dbname;
            $this->table = false;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    public function close() {
        $this->handle = null;
        return $this;
    }

    public function __destruct() {
        $this->close();
    }

    public function query(string $sql) {
        try {
            $stmt = $this->handle->prepare($sql);
            $a = $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function useDataBase(string $db) {

        try {
            $sth = $this->handle->prepare('use ' . $db);
            $this->handle->exec("use " . $db);
            $this->db = $db;
        } catch (PDOException $e) {
            throw $e;
        }

        return $this;

    }

    public function getHandle() {
        return $this->handle;
    }

    public function useTable(string $table) {
        $this->table = $table;
        return $this;
    }

    public function setAttribute($key, $value) {
        $this->handle->setAttribute($key, $value);
    }

}