<?php
namespace Charlotte\ORM;

class DalAdapter implements DalInterface{

    private $handle;
    private $db;
    private $table;
    private $tables;
    private $dbs;

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

    /**
     * @inheritdoc
     */
    public function connect($host, $user, $pass, $dbname, $port = '3306', $driver = 'mysql') {
        try {
            $dsn = sprintf("%s:host=%s;dbname=%s;port=%s", $driver, $host, $dbname, $port);
            $this->handle = new \PDO($dsn, $user, $pass, array(\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, \PDO::ATTR_PERSISTENT => true));
            $this->handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->db = $dbname;
            $this->table = '';
            $this->tables = array();
            $this->dbs = array();
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function close() {
        $this->reset();
        return $this;
    }

    /**
     *
     */
    protected function reset() {
        $this->handle = null;
        $this->db = '';
        $this->table = '';
        $this->tables = array();
        $this->dbs = array();
    }

    /**
     *
     */
    public function __destruct() {
        $this->close();
    }

    /**
     * @inheritdoc
     */
    public function query(string $sql, $bindings = array()) {
        try {
            $this->handle->beginTransaction();
            $stmt = $this->handle->prepare($sql);
            $a = $stmt->execute($bindings);
            $this->handle->commit();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw $e;
            
        }
    }


    /**
     * @inheritdoc
     */
    public function update(string $sql, array $bindings = array()) {
        try {
            $this->handle->beginTransaction();
            $stmt = $this->handle->prepare($sql);
            $a = $stmt->execute($bindings);
            $this->handle->commit();
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw $e;          
        }
    }

    /**
     * @param string $db
     * @return $this
     * @throws \Exception
     */
    public function useDataBase(string $db) {

        try {
            if (count($this->dbs) === 0) {
                $this->retrieveDatabases();
            }
            if (in_array($db, $this->dbs)) {
                $this->handle->beginTransaction();
                $sth = $this->handle->prepare('use ' . $db);
                $this->handle->exec("use " . $db);
                $this->handle->commit();
                $this->db = $db;
            } else {
                throw new \Exception('database not found: ' . $db);
            }

        } catch (\PDOException $e) {
            throw $e;
        }

        return $this;

    }

    /**
     * @param string $table
     * @return $this
     * @throws \Exception
     */
    public function useTable(string $table) {
        if (count($this->tables) < 1) {
            $this->retrieveTables();
        }
        if (in_array($table, $this->tables)) {
            $this->table = $table;
        } else {
            throw new \Exception('table not found: ' . $table);
        }
        
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function retrieveTables() {

        if (!$this->db && $this->db !== '') {
            throw new \Exception('Database not chosen', 500);
        }

        try {
            $this->handle->beginTransaction();
            $stmt = $this->handle->prepare('SHOW TABLES');
            $stmt->execute();
            $this->handle->commit();
            $temp = $stmt->fetchAll();

            if (count($this->tables) > 0) {
                $this->tables = array();
            }

            foreach($temp as $table) {
                $this->tables[] = $table['Tables_in_' . $this->db];
            }
        } catch (\PDOException $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function retrieveDatabases() {
        try {
            $this->handle->beginTransaction();
            $stmt = $this->handle->prepare('SHOW DATABASES');
            $stmt->execute();
            $this->handle->commit();
            $temp = $stmt->fetchAll();

            if ($this->dbs === null) {
                $this->dbs = array();
            }

            if (count($this->dbs) > 0) {
                $this->dbs = array();
            }

            foreach($temp as $db) {
                $this->dbs[] = $db['Database'];
            }

        } catch (\PDOException $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHandle() {
        return $this->handle;
    }

    /**
     * @return mixed
     */
    public function getDatabases() {
        return $this->dbs;
    }

    /**
     * @return mixed
     */
    public function getTables() {
        return $this->tables;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value) {
        $this->handle->setAttribute($key, $value);
    }

    /**
     * @return mixed
     */
    public function getDataBase() {
        return $this->db;
    }

}