<?php
namespace Charlotte\ORM;

use Charlotte\ORM\Query;
use Charlotte\Core\Config;
class Mapper implements MapperInterface {

    protected const LOAD = 1000;

    protected $adapter;

    protected $query;
    
    protected $cache;

    protected $table;

    public const TABLE_STRUCTURE = 1;

    public const TABLE_RECORDS = 2;

    public const TABLE_COMMITS_INSERTS = 3;

    public const TABLE_COMMITS_UPDATES = 4;
    
    public const TABLE_COMMITS_DELETE = 5;
    
    public const TABLE_COMMITS_STRUCTURE = 6;

    public const TABLE_QUERIES = 7;

    protected $default_load;

    protected $autosave;

    protected const TABLE_PLACEHOLDER_START = '{{@';

    protected const TABLE_PLACEHOLDER_END = '@}}';

    protected $insert_if_not_existing = false;

    protected $update_if_existing = false;

    protected $id;


    /**
     * Mapper constructor.
     * @param Config|null $config
     * @param DalAdapter|null $adapter
     */
    public function __construct(Config $config = null, DalAdapter $adapter = null)
    {
        $this->adapter = &$adapter;
        $this->default_load = self::LOAD;
        $this->clearCache();
        $this->query = new Query();
        $this->autosave = false;
        // the default id
        $this->id = 'id';

        if ($config instanceof Config) {
            if ($config->has('container->default_load')) {
                $this->default_load = (int)$config->get('container->default_load');
            }

            if ($config->has('container->autosave')) {
                $this->autosave = (bool)$config->get('container->autosave');
            }

            if ($config->has('container->insert_if_not_existing')) {
                $this->insert_if_not_existing = (bool)$config->get('container->insert_if_not_existing');
            }

            if ($config->has('container->update_if_existing')) {
                $this->update_if_existing = (bool)$config->get('container->update_if_existing');
            }
        }
    }

    // TODO: Mapper: add default values if null or empty as required, preferred implementation in entity

    /**
     * @param bool $force
     * @return $this
     */
    public function commit($force = false) {

        if (count($this->cache[self::TABLE_COMMITS_INSERTS]) > 0) {
            $inserts = array();
            $non_null_column = DBTypes::getNotNullValues($this->cache[self::TABLE_STRUCTURE]);
            $columnList = !empty($non_null_column) ? '('.implode(', ', $non_null_column).')' : '';
            $rowPlaceholder = ' ('.implode(', ', array_fill(1, count($non_null_column), '?')).')';
    
            $sql = sprintf(
                'INSERT INTO `%s`.`%s` %s VALUES %s',
                $this->adapter->getDataBase(),
                $this->table,
                $columnList,
                implode(', ', array_fill(1, count($this->cache[self::TABLE_COMMITS_INSERTS]), $rowPlaceholder))
            );
    
            foreach($this->cache[self::TABLE_COMMITS_INSERTS] as $row) {
                foreach($row as $k => $v) {
                    if (in_array($k, $non_null_column)) {
                        $inserts[] = $v;
                    }
                }
            }
            
            $this->query->addQuery($sql, $inserts);
        }
        
        if (count($this->cache[self::TABLE_COMMITS_UPDATES]) > 0) {
            // TODO: Mapper commit: add commit logic for updates
            $properties = array_keys($this->cache[self::TABLE_RECORDS][0]);

            $sql = sprintf(
                'UPDATE `%s`.`%s` SET ' . self::TABLE_PLACEHOLDER_START. self::TABLE_PLACEHOLDER_END,
                $this->adapter->getDataBase(),
                $this->table
            );

            $id_index = array_search($this->id, $properties);
            if ($id_index > 0) {
                $temp = $properties[$id_index];
                $properties[$id_index] = $properties[0];
                $properties[0] = $temp;

            }

            for ($index = 0; $index < count($properties); $index++) {
                
                if ($index < count($properties) - 1) {

                    if ($properties[$index] === $this->id) {
                        continue;
                    }

                    $sql = str_replace(self::TABLE_PLACEHOLDER_START. self::TABLE_PLACEHOLDER_END, 
                                       '`' . $properties[$index] . '` = (CASE ' .  $this->id . ' ' . self::TABLE_PLACEHOLDER_START. $properties[$index] . self::TABLE_PLACEHOLDER_END . ' END), ' . self::TABLE_PLACEHOLDER_START. self::TABLE_PLACEHOLDER_END,
                                       $sql
                    );
                } else {

                    $sql = str_replace(self::TABLE_PLACEHOLDER_START. self::TABLE_PLACEHOLDER_END, 
                    '`' . $properties[$index] . '` = (CASE ' .  $this->id . ' ' . self::TABLE_PLACEHOLDER_START. $properties[$index] . self::TABLE_PLACEHOLDER_END . ' END) WHERE ' . $this->id . ' IN ( ' . self::TABLE_PLACEHOLDER_START. $this->id . self::TABLE_PLACEHOLDER_END . ')',
                    $sql
                    );

                }
            }
            
            $updates = array();
            $lastElement = end($this->cache[self::TABLE_COMMITS_UPDATES]);

            foreach($this->cache[self::TABLE_COMMITS_UPDATES] as $row) {

                $indx = array_search($row, $this->cache[self::TABLE_COMMITS_UPDATES]);
                $record = $this->cache[self::TABLE_RECORDS][$indx];
                
                foreach ($record as $key => $value) {

                    if ($key === $this->id) {
                        continue;
                    }

                    if (array_key_exists($key, $row)) {
                        $record[$key] = $row[$key];
                    }

                    $sql = str_replace(self::TABLE_PLACEHOLDER_START. $key . self::TABLE_PLACEHOLDER_END,
                            ' WHEN ' . $record[$this->id] . ' THEN ? ' . self::TABLE_PLACEHOLDER_START. $key . self::TABLE_PLACEHOLDER_END,
                            $sql);
                    $updates[] = $record[$key];
                }
                $sql = str_replace(self::TABLE_PLACEHOLDER_START. $this->id . self::TABLE_PLACEHOLDER_END,
                        $record[$this->id] . ($row === $lastElement ? '' : ', ') .self::TABLE_PLACEHOLDER_START. $this->id . self::TABLE_PLACEHOLDER_END,
                        $sql);
            }

            foreach($properties as $value) {
                $sql = str_replace(self::TABLE_PLACEHOLDER_START. $value . self::TABLE_PLACEHOLDER_END, '', $sql);
            }
            
            $sql = str_replace(self::TABLE_PLACEHOLDER_START. $this->id . self::TABLE_PLACEHOLDER_END, '', $sql);
            $this->query->addQuery($sql, $updates);
        }
        $this->postCommit();

        // TODO: Mapper commit: add similar logic to drop, delete, and more
        return $this;
    }

    /**
     * @return $this
     */
    public function persist() {
        $result = 0;
        if ($this->query->size() > 0) {
            $size = $this->query->size();
            for ($i = 0; $i < $size; $i++) {
                $sql = $this->query->getQuery();
                $bindings = $this->query->getBindings();              
                $result += $this->adapter->update($sql, $bindings);
            }
            $this->query->reset();
        }
        
        // TODO: Mapper persist: update the logic if not all queries are executed successfully
        // if (count($this->cache[self::TABLE_COMMITS_UPDATES]) + count($this->cache[self::TABLE_COMMITS_INSERTS]) !== $result) {
        //     //var_dump(count($this->cache[self::TABLE_COMMITS_UPDATES]) + count($this->cache[self::TABLE_COMMITS_INSERTS]), $result);
        //     //throw new \Exception('sync error', 500);
        // } else {

        // }
        return $this;
    }

    /**
     * @param string $id
     */
    public function useID($id = 'id') {
        $this->id = $id;
    }

    /**
     * Some cleaning work after commit
     */
    protected function postCommit() {
        $this->cache[self::TABLE_COMMITS_INSERTS] = array();
        $this->cache[self::TABLE_COMMITS_UPDATES] = array();
        $this->cache[self::TABLE_COMMITS_DELETE] = array();
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
            self::TABLE_COMMITS_DELETE => array(),
            self::TABLE_COMMITS_STRUCTURE => array(),
            self::TABLE_RECORDS => array()
        );
        return $this;
    }

    // public function clearUnsavedCache() {

    // }

    /**
     * @param DalAdapter $adapter
     * @return Mapper
     */
    public function useAdapter(DalAdapter $adapter) {
        $this->adapter = &$adapter;
        return $this;
    }


    public static function importFrom() {
         // TODO: Mapper static create mapper instance directly from another mapper instance
    }

    /**
     * 
     */
    public function setAutosave(bool $autosave) {
        $this->autosave = $autosave;
        return $this;
    }

    /**
     * @param array $properties
     * @return bool
     */
    public function hasUnsavedCache(array $properties = array()) {
        if (count($properties) < 1) {
            return count($this->cache[self::TABLE_COMMITS_DELETE]  )> 0 ||
                    count($this->cache[self::TABLE_COMMITS_INSERTS]  ) > 0||
                    count($this->cache[self::TABLE_COMMITS_UPDATES]  )> 0;
        } else {
            foreach ($properties as $property) {
                if (count($this->cache[$properties]  )> 0) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * @param string $db
     * @return Mapper
     * @throws \Exception
     */
    public function useDatabase(string $db = '') {
        if ($db !== '') {
            if ( $this->hasUnsavedCache()) {
                $this->commit();
            }
            $this->persist();
        }
        $this->adapter->useDatabase($db);
        return $this;
    }

    /**
     * @param string $table
     *
     * @return Mapper
     * @throws \Exception
     */
    public function useTable(string $table = '') {

        if($table !== '' && $this->table !== $table) {
            $this->commit();
        }
        $result = $this->adapter->useTable($table);

        if ($result instanceof DalAdapter) {
            $this->table = $table;
        }
        return $this;
    }

    // /**
    //  * @param string $table
    //  * @param bool $force
    //  * @return mixed
    //  */
    // public function getMapping(string $table = '', bool $force = false) {
    //     if (count($this->cache[self::TABLE_STRUCTURE]) > 0 && !$force) {
    //         return $this->cache[self::TABLE_STRUCTURE];
    //     }

    //     $table = $table !== ''? $table : $this->table;
    //     $mapping = $this->adapter->query('DESCRIBE '. $table);

    //     foreach ($mapping as $key => $value) {
    //         $mapping[$value['Field']] =$value;
    //         unset($mapping[$key]);
    //     }
    //     if ($table ===  $this->table) {
    //         $this->cache[self::TABLE_STRUCTURE] = $mapping;
    //     }
    //     return $mapping;
    // }

    /**
     * @param string $table
     * @param bool $force force to get new cache even if there is cache in place
     * @return mixed
     */
    public function getMappingAdvance(string $table = '', bool $force = false) {

        if (count($this->cache[self::TABLE_STRUCTURE]) > 0 && !$force) {
            return $this->cache[self::TABLE_STRUCTURE];
        }
        $table = $table !== ''? $table : $this->table;
        $mapping = $this->adapter->query('SHOW FULL COLUMNS FROM ' . $table);
        
        foreach ($mapping as $key => $value) {
            $mapping[$value['Field']] =$value;
            unset($mapping[$key]);
        }

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
        // if this is a single record
        if (self::has_string_keys($value)) {
            if (in_array($property, [self::TABLE_COMMITS_DELETE, self::TABLE_COMMITS_UPDATES])) {

            } elseif ($property === self::TABLE_COMMITS_INSERTS) {

            }

            $indeces = $this->findIndcesBy($value, $property, $prikeys);
            $indeces_record =  $this->findIndcesBy($value, self::TABLE_RECORDS, $prikeys);
            
            if ($property === self::TABLE_COMMITS_INSERTS) {
                if(count($indeces_record) > 0) {
                    $indeces_in_updates = $this->findIndcesBy($value, self::TABLE_COMMITS_UPDATES, $prikeys);
                    if ($this->update_if_existing) {
                        $this->cache[self::TABLE_COMMITS_UPDATES][$indeces_record[0]] = $value;
                    }

                } elseif (count($indeces) > 0) {
                    $this->cache[self::TABLE_COMMITS_INSERTS][$indeces[0]] = $value;
                } else {
                    $this->cache[self::TABLE_COMMITS_INSERTS][] =  $value;
                }
            }  elseif (in_array($property, [self::TABLE_RECORDS, self::TABLE_STRUCTURE])) {
                if(count($indeces) > 0) {
                    $this->cache[$property][$indeces[0]] = $value;
                } else{
                    $this->cache[$property][] =  $value;
                }
            } else {
                if(count($indeces) > 0) {
                    $this->cache[$property][$indeces[0]] = $value;
                } elseif(count($indeces_record) > 0) {
                    // TODO: deal with the situation that same record is in updates and delete at meantime
                    $this->cache[$property][$indeces_record[0]] =  $value;
                } elseif ($this->insert_if_not_existing && $property === self::TABLE_COMMITS_UPDATES) {
                    $this->cache[self::TABLE_COMMITS_INSERTS][] =  $value;
                }
            }

        } else { // if this is bulk of records
            foreach($value as $v) {
                $indeces = $this->findIndcesBy($v, $property, $prikeys);
                $indeces_record =  $this->findIndcesBy($v, self::TABLE_RECORDS, $prikeys);

                if ($property === self::TABLE_COMMITS_INSERTS) {
                    if(count($indeces_record) > 0) {
                        $indeces_in_updates = $this->findIndcesBy($v, self::TABLE_COMMITS_UPDATES, $prikeys);
                        if ($this->update_if_existing) {
                            $this->cache[self::TABLE_COMMITS_UPDATES][$indeces_record[0]] = $v;
                        }
    
                    } elseif (count($indeces) > 0) {
                        $this->cache[self::TABLE_COMMITS_INSERTS][$indeces[0]] = $v;
                    } else {
                        $this->cache[self::TABLE_COMMITS_INSERTS][] =  $v;
                    }
                }  elseif (in_array($property, [self::TABLE_RECORDS, self::TABLE_STRUCTURE])) {
                    if(count($indeces) > 0) {
                        $this->cache[$property][$indeces[0]] = $v;
                    } else{
                        $this->cache[$property][] =  $v;
                    }
                } else {
                    if(count($indeces) > 0) {
                        $this->cache[$property][$indeces[0]] = $v;
                    } elseif(count($indeces_record) > 0) {
                        // TODO: deal with the situation that same record is in updates and delete at meantime
                        $this->cache[$property][$indeces_record[0]] =  $v;
                    } elseif ($this->insert_if_not_existing && $property === self::TABLE_COMMITS_UPDATES) {
                        $this->cache[self::TABLE_COMMITS_INSERTS][] =  $v;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param string $table
     * @param array $where
     * @param bool $force reset the cache TABLE_RECORDS if true
     * @return $this
     * @throws \Exception
     */
    public function retrieveData(string $table = '', array $where = array(), bool $force = true) {
        if ($table !== '') {
            $this->useTable($table);
        }
        // reset the cache to empty array if forced
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
     * Main function to find record in certain array
     * @param array $where
     * @param array $data
     * @param array $prikeys
     * @return array
     */
    protected function findByMain(array $where = array(), array $data = array(), array $prikeys = array()) {
        $result = array();
        foreach($data as $item) {
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
     * Main function to find index of record in a certain array
     * @param array $where
     * @param array $data
     * @param array $prikeys
     * @return array
     */
    protected function findIndcesByMain(array $where = array(), array $data = array(), array $prikeys = array()) {
        $result = array();
        
        foreach($data as $index => $item) {
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
     * @param array $where
     * @param int $property
     * @param array $prikeys
     * @return array
     */
    protected function findBy(array $where = array(), $property = self::TABLE_RECORDS, array $prikeys = array()) {
        return $this->findByMain($where, $this->cache[$property], $prikeys);
    }

    /**
     * @param array $where
     * @param int $property
     * @param array $prikeys
     * @return array
     */
    protected function findIndcesBy(array $where = array(), $property = self::TABLE_RECORDS, array $prikeys = array()) {
        return $this->findIndcesByMain($where, $this->cache[$property], $prikeys);
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

        $entity = new $class($this->getMappingAdvance(), $this, array(
            'data_types' => DBTypes::getDataTypes( $this->cache[self::TABLE_STRUCTURE]),
            'primary_keys' => DBTypes::getPrimaryKeys($this->cache[self::TABLE_STRUCTURE]),
            'not_null_columns' => DBTypes::getNotNullValues($this->cache[self::TABLE_STRUCTURE])
        ));

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
        if($this->autosave === true) {
            $this->commit();
            if($this->query->size() > 0) {
                $this->persist();
            }
        }
    }

    /**
     * @param array $array
     * @return bool
     */
    public static function has_string_keys(array $array) {
        // TODO: move the utility helper to a shared library or utility class
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

}