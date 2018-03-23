<?php
namespace Charlotte\ORM;

class Mapper implements MapperInterface {

    protected $adapter;

    protected $query;
    
    protected $cache;

    protected $table;

    public const TABLE_STRUCTURE = 1;

    public const TABLE_RECORDS = 2;

    public const TABLE_COMMITS_INSERTS = 3;

    public const TABLE_COMMITS_UPDATES = 4;

    public const TABLE_QUERIES = 5;

    protected $default_load;

    public function __construct(DalAdapter $adapter = null, int $default_load = 1000)
    {
        $this->adapter = $adapter;
        $this->default_load = $default_load;
        $this->clearCache();
    }


    public function commit($force = false) {

    }

    public function persist() {

    }

    /**
     *
     */
    public function clearCache() {
        $this->cache = array(
            self::TABLE_QUERIES => array(),
            self::TABLE_STRUCTURE => array(),
            self::TABLE_COMMITS_INSERTS => array(),
            self::TABLE_COMMITS_UPDATES => array(),
            self::TABLE_RECORDS => array()
        );

        return $this;
    }

    /**
     * @param DalAdapter $adapter
     * @return Mapper
     */
    public function useAdapter(DalAdapter $adapter) {
        $this->adapter = $adapter;
        return $this;
    }

    // public static function importFrom() {

    // }

    /**
     * @param string $db
     * @return Mapper
     * @throws \Exception
     */
    public function useDatabase(string $db) {
        $this->adapter->useDatabase($db);
        return $this;
    }

    /**
     * @param string $table
     *
     * @return Mapper
     * @throws \Exception
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
    public function getMapping(string $table = '', bool $force = false) {
        if (count($this->cache[self::TABLE_STRUCTURE]) > 0 && !$force) {
            return $this->cache[self::TABLE_STRUCTURE];
        }

        $table = $table !== ''? $table : $this->table;
        $mapping = $this->adapter->query('DESCRIBE '. $table);
        if ($table ===  $this->table) {
            $this->cache[self::TABLE_STRUCTURE] = $mapping;
        }
        return $mapping;
    }

    /**
     * @param string $table
     * @return mixed
     */
    public function getMappingAdvance(string $table = '', bool $force = false) {

        if (count($this->cache[self::TABLE_STRUCTURE]) > 0 && !$force) {
            return $this->cache[self::TABLE_STRUCTURE];
        }
        $table = $table !== ''? $table : $this->table;
        $mapping = $this->adapter->query('SHOW FULL COLUMNS FROM ' . $table);
        if ($table ===  $this->table) {
            $this->cache[self::TABLE_STRUCTURE] = $mapping;
        }
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
     * @return Mapper
     * @throws \Exception
     */
    public function retrieveDataBases() {
        $this->adapter->retrieveDataBases();
        return $this;
    }

    /**
     * @return Mapper
     * @throws \Exception
     */
    public function retrieveTables() {
        $this->adapter->retrieveTables();
        return $this;
    }


    /**
     * @param int $property
     * @param array $value
     * @param array $prikeys
     * @return $this
     */
    public function addCache($property = self::TABLE_RECORDS , array $value, array $prikeys = array()) {

        if (self::has_string_keys($value)) {
            $indeces = $this->findIndcesBy($value, $property, $prikeys);
            if(count($indeces) > 0) {
                $this->cache[$property][$indeces[0]] = $value;
            } else {
                $this->cache[$property][] =  $value;
            }
        } else {
            foreach($value as $v) {
                $indeces = $this->findIndcesBy($v, $property, $prikeys);
                if(count($indeces) > 0) {
                    $this->cache[$property][$indeces[0]] = $v;
                } else {
                    $this->cache[$property][] =  $v;
                }
            }
        }
        return $this;
    }

    /**
     * @param string $table
     * @param array $where
     * @param bool $force
     * @return $this
     * @throws \Exception
     */
    public function retrieveData(string $table = '', array $where = array(), bool $force = true) {
        if ($table !== '') {
            $this->useTable($table);
        }

        if($force === true) {
            $this->cache[self::TABLE_RECORDS] = array();
        }

        if (count($where) > 0) {
            $w = "";
            $size = count($where);
            $index = 0;
            foreach ($where as $key => $value) {
                $w .= $key . '=' . $value;
                if ($index < $size - 1) {
                    $w .= ' AND ';
                }
                $index++;
            }
            $sql = sprintf("SELECT * FROM %s WHERE %s LIMIT %d", $this->table, $w,  $this->default_load);
        } else {
            $sql = sprintf("SELECT * FROM %s LIMIT %d", $this->table, $this->default_load);
        }

        $this->addCache(self::TABLE_RECORDS, $this->adapter->query( $sql));
        return $this;
    }


    /**
     * @param string $key
     * @return mixed
     */
    public function getCache(string $key = '') {
        if ($key !== '' && array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        } else {
           return $this->cache; 
        }
    }

    /**
     * @param array $where
     * @param int $property
     * @param array $prikeys
     * @return array
     */
    public function findBy(array $where = array(), $property = self::TABLE_RECORDS, array $prikeys = array()) {
        $result = array();
        foreach($this->cache[$property] as $item) {
            $flag = true;
            
            foreach($where as $key => $value) {
                if (count($prikeys) > 0 && !in_array($key, $prikeys)) {
                    continue;
                }
                if (array_key_exists($key, $item) && $item[$key] !== $value) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
              $result[] = $item;  
            }
        } 
        return $result;
    }

    /**
     * @param array $where
     * @param int $property
     * @param array $prikeys
     * @return array
     */
    public function findIndcesBy(array $where = array(), $property = self::TABLE_RECORDS, array $prikeys = array()) {
        $result = array();
        
        foreach($this->cache[$property] as $index => $item) {
            $flag = true;
            
            foreach($where as $key => $value) {
                if (count($prikeys) > 0 && !in_array($key, $prikeys)) {
                    continue;
                }
                
                if (array_key_exists($key, $item) && $item[$key] !== $value) {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
              $result[] = $index;  
            }
        } 
        return $result;
    }


    /**
     * @param string $class
     * @param array $where
     * @param string $table
     * @param bool $force_refresh
     * @param bool $refresh_cache
     * @return mixed
     * @throws \Exception
     */
    public function createEntityFromCache(string $class, array $where = array(), string $table = '', bool $force_refresh = false, bool $refresh_cache = true) {
        if( $force_refresh === true || count($this->cache[self::TABLE_RECORDS]) === 0) {
            $this->retrieveData($table, $where, $refresh_cache);
        }
        $result = $this->findBy($where); 

        $entity = new $class($this->getMappingAdvance(), $this);
        if ($entity instanceof AbstractEntity) {
            $entity->use($result[0]);
        } elseif ($entity instanceof EntityContainer) {
           $entity->use($result); 
        } else {
            throw new \Exception($class . ' is not entity or entity container', 500);
        }
        
        return $entity;
    }


    /**
     * @param AbstractEntity $entity
     * @param string $property
     * @param bool $search_in_db
     * @return array
     * @throws \Exception
     */
    public function exists(AbstractEntity  $entity, string $property = self::TABLE_RECORDS, bool $search_in_db = false) {
        $params = $entity->buildParams();
        $exists_in_cache = $this->findBy($params, $property);

        if (!$exists_in_cache && $search_in_db) {
            $this->retrieveData('', $params, false);
            $exists_in_cache = $this->findBy($params, $property);
        }
        return $exists_in_cache;
    }
    
    /**
     * Need to commit and persist before destroying the object
     */
    public function __destruct() {
        $this->commit();
        $this->persist();
    }

    /**
     * @param array $array
     * @return bool
     */
    public static function has_string_keys(array $array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

}