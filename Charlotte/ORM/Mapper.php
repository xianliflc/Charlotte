<?php
namespace Charlotte\ORM;

class Mapper implements MapperInterface {

    protected $adapter;

    protected $query;
    
    protected $cache;

    protected $table;

    public const TABLE_STRUCTURE = 1;

    public const TABLE_RECORDS = 2;

    public const TABLE_QUERIES = 3;

    protected $default_load;

    public function __construct(int $default_load = 1000)
    {
        $this->default_load = $default_load;
        $this->cache = array();
    }
    // public function commit() {

    // }

    // public function persist() {

    // }

    /**
     *
     */
    public function clearCache() {
        $this->cache = array();

        return $this;
    }

    // public function find(...$params) {

    // }

    /**
     * @param DalAdapter $adapter
     */
    public function useAdapter(DalAdapter $adapter) {
        $this->adapter = $adapter;
        return $this;
    }

    // public static function importFrom() {

    // }

    /**
     * @param string $db
     */
    public function useDatabase(string $db) {
        $this->adapter->useDatabase($db);
        return $this;
    }

    /**
     * @param string $table
     */
    public function useTable(string $table) {
        $result = $this->adapter->useTable($table);

        if ($result instanceof DalAdapter) {
            $this->table = $table;
        }
        return $this;
    }

    /**
     * @param string $table
     * @return mixed
     */
    public function getMapping(string $table = '') {
        $table = $table !== ''? $table : $this->table;
        $mapping = $this->adapter->query('DESCRIBE '. $table);

        return $mapping;
    }

    /**
     * @param string $table
     * @return mixed
     */
    public function getMappingAdvance(string $table = '') {
        $table = $table !== ''? $table : $this->table;
        $mapping = $this->adapter->query('SHOW FULL COLUMNS FROM ' . $table);
        
        return $mapping;
    }

    /**
     * @return array
     */
     public function getDatabases() {
         return $this->adapter->getDatabases();
     }

    /**
     * @return array
     */
     public function getTables() {
         return $this->adapter->getTables();
     }

    /**
     * @return DalAdapter
     */
    public function retrieveDataBases() {
        $this->adapter->retrieveDataBases();
        return $this;
    }

    /**
     * @return DalAdapter
     */
    public function retrieveTables() {
        $this->adapter->retrieveTables();
        return $this;
    }


    public function addCache($property = self::TABLE_RECORDS , array $value) {

        $this->cache[$property] = $value;
        return $this;
    }

    public function retrieveData(string $table = '') {
        if ($table !== '') {
            $this->useTable($table);
        }

        if(count($this->cache) > 0) {
            $this->cache = array();
        }
        $sql = sprintf("SELECT * FROM %s LIMIT %d", $this->table, $this->default_load);
        $this->cache = $this->adapter->query( $sql);
        return $this;
    }

    public function getCache() {
        return $this->cache;
    }


}