<?php

namespace Charlotte\ORM;
// TODO: check properties and the types defined (limit, type match, default value)
class AbstractEntity implements EntityInterface{

    protected $properties;
    protected $hasError;
    protected $isBuilt;
    protected $mapper;
    protected $existing;
    protected $priKeys;
    protected $dataTypes;
    protected $notNullProperties;



    public function __construct($properties = array(), Mapper &$mapper = null, array $options = array())
    {
        $this->properties = $properties;
        $this->hasError = false;
        $this->isBuilt = false;
        $this->existing = false;
        $this->mapper = $mapper;

        foreach($options as $key => $value) {
            if (gettype($value) === 'array' && in_array(strtolower($key), ['data_types', 'primary_keys', 'not_null_columns'])) {
                switch ($key) {
                    case 'data_types':
                        $this->dataTypes = $value;
                        break;
                    case 'primary_keys':
                        $this->priKeys = $value;
                        break;
                    case 'not_null_columns':
                        $this->notNullProperties = $value;
                        break;
                }
            }
        }

        if ($this->dataTypes  === null) {
            $this->dataTypes = $this->setPropertyDataTypes();
        }

        if ($this->priKeys === null) {
            $this->priKeys = $this->setPrimaryKeys();
        }

        if ($this->notNullProperties === null) {
            $this->notNullProperties = $this->setNotNullProperties();
        }

    }


    public function getProperties() {
        return $this->properties;
    }

    public function setMapper(Mapper &$mapper) {
        $this->mapper = $mapper;
        return $this;
    }

    public function getPriKeys() {
        return $this->priKeys;
    }

    public function getDataTypes() {
        return $this->dataTypes;
    }

    public function getNotNullProperties() {
        return $this->notNullProperties;
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

        //TODO: update the logic that validating the entity based on primary keys having valid values
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
     * @return $this
     */
    public function save(Mapper &$mapper = null) {
        if (is_null($mapper) ) {
            $mapper = &$this->mapper;
        }

        if ($this->isValid() && $this->arePropertiesValid()) {
            $mapper->addCache($this->existing? Mapper::TABLE_COMMITS_UPDATES : Mapper::TABLE_COMMITS_INSERTS, 
                                $this->buildParams(), $this->priKeys);
        } else {
            throw new \Exception('Properties are invalid', 500);
        }


        return $this;
    }

    public function arePropertiesValid() {
        $this->fillDefaultValues();

        foreach($this->properties as $key => $value) {
            if ((is_null($this->{$key}) && in_array($key, $this->notNullProperties)) ||gettype($this->{$key}) !== $this->dataTypes[$key]) {
                return false;
            }
        }

        return true;
    }

    protected function fillDefaultValues(){
        // TODO: add default values if value in absence 
    }

    protected function setPropertyDataTypes() {
        return DBTypes::getDataTypes($this->properties);
    }

    protected function setNotNullProperties() {
        return DBTypes::getNotNullValues($this->properties);
    }

    protected function setPrimaryKeys() {
        return DBTypes::getPrimaryKeys($this->properties);
    }

}