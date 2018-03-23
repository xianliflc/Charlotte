<?php

namespace Charlotte\ORM;

class AbstractEntity implements EntityInterface{

    protected $properties;
    protected $hasError;
    protected $isBuilt;
    protected $mapper;
    protected $existing;
    protected $priKeys;

    public function __construct($properties = array(), Mapper &$mapper = null)
    {
        $this->properties = $properties;
        
        $this->formatProperties();
        $this->hasError = false;
        $this->isBuilt = false;
        $this->existing = false;
        $this->mapper = $mapper;
        $this->getPriKeys();
    }


    public function getProperties() {
        return $this->properties;
    }

    public function setMapper(Mapper &$mapper) {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     *
     */
    protected function getPriKeys() {
        $this->priKeys = array();
        foreach($this->properties as $key => $value) {
            if ($value['Key'] === 'PRI') {
                $this->priKeys[] = $key;
            }
        }
    }

    /**
     * @param array $data
     * @param bool $existing
     * @return EntityInterface
     */
    public function use(array $data = array(), $existing = true) : EntityInterface {
        
        $result = true;
        foreach ($data as $key => $value) {
            if (!$this->has($key)) {
                $result = false;
            } else {
                $this->{$key} = $value;
            }
        }
        
        if (!$result) {
            $this->hasError = true;
        }
        $this->isBuilt = true;
        $this->existing = $existing;
        return $this;
    }

    /**
     * @param string $name
     * @param string $privilege
     * @return bool
     */
    public function ableTo($name = '', $privilege = '') : bool {
        if ($name === '' || $privilege === '') {
            return false;
        }

        if (property_exists($this, $name) && array_key_exists($name, $this->properties) &&  strpos($this->properties[$name]['Privileges'], $privilege) !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name) {
        return property_exists($this, $name) && array_key_exists($name, $this->properties);
    }


    /**
     *
     */
    protected function formatProperties() {
        foreach ($this->properties as $key => $value) {
            $this->properties[$value['Field']] =$value;
            unset($this->properties[$key]);
        }
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name) {
        if ($this->has($name)) {
            return $this->$name;
        } else {
            throw new \Exception($name . ': Property not existing or not accessible', 500);
        }
    }

    /**
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function __set($name, $value) {

        if ( $this->has($name) && $this->ableTo($name, 'update') && $this->ableTo($name, 'insert')) {
            $this->$name = $value;
        } else {
            throw new \Exception($name . ': Property not existing or overwritting is not allowed', 500);
        }
    }

    public function __destruct() {
        $this->save();
        $this->mapper = null;
    }

    /**
     * @return bool
     */
    public function isValid(): bool {
        return !$this->hasError && $this->isBuilt;
    }

    /**
     * @return array
     */
    public function buildParams() {
        $result =array();
        foreach($this->properties as $key => $value) {
            if ($this->existing && $this->ableTo($key, 'update')) {
                $result[$key] = $this->{$key};
            } elseif (!$this->existing && $this->ableTo($key, 'insert')) {
                $result[$key] = $this->{$key};
            }
        }
        return $result;
    }

    /**
     * @param Mapper|null $mapper
     */
    public function save(Mapper &$mapper = null) {
        if (is_null($mapper) ) {
            $mapper = &$this->mapper;
        }

        $mapper->addCache($this->existing? Mapper::TABLE_COMMITS_UPDATES : Mapper::TABLE_COMMITS_INSERTS, 
                            $this->buildParams(), $this->priKeys);

        return $this;
    }

}