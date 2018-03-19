<?php
namespace Charlotte\ORM;

class Mapper implements MapperInterface {

    public $connection;

    public $query;
    
    public $cache;

    // public function commit() {

    // }

    // public function persist() {

    // }

    /**
     *
     */
    public function clearCache() {
        $this->cache = array();
    }

    // public function find(...$params) {

    // }

    /**
     * @param \PDO $conn
     */
    public function useConnection(\PDO $conn) {
        $this->connection = $conn;
    }

    // public static function importFrom() {

    // }

    /**
     * @param string $db
     */
    public function useDatabase(string $db) {
        $this->connection->useDatabase($db);
    }

    /**
     * @param string $table
     */
    public function useTable(string $table) {
        $this->connection->useTable($table);
    }
}