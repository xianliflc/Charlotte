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
    // check if the enitity has any updates since it got cretaed
    protected $hasUpdateSinceCreated;
    protected $mandatoryPriKeys;


    /**
     * AbstractEntity constructor.
     * @param array $properties
     * @param Mapper|null $mapper
     * @param array $options
     */
    public function __construct($properties = array(), Mapper $mapper = null, array $options = array())
    {
        $this->properties = $properties;
        $this->hasError = false;
        $this->isBuilt = false;
        $this->existing = false;
        $this->mapper = $mapper;

        foreach($options as $key => $value) {
            if (gettype($value) === 'array' && in_array(strtolower($key), ['data_types', 'primary_keys', 'not_null_columns'])) {
                switch ($key) {
                    // TODO: add more available options if neccessary
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
            $this->dataTypes = $this->parsePropertyDataTypes();
        }

        if ($this->priKeys === null) {
            $this->priKeys = $this->parsePrimaryKeys();
        }

        if ($this->mandatoryPriKeys === null) {
            $this->mandatoryPriKeys = $this->parseMandatoryPrimaryKeys();
        }

        if ($this->notNullProperties === null) {
            $this->notNullProperties = $this->parseNotNullProperties();
        }

    }

    /**
     * @return array
     */
    public function getProperties() {
        return $this->properties;
    }

    /**
     * @param Mapper $mapper
     * @return $this
     */
    public function setMapper(Mapper &$mapper) {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return array
     */
    public function getPriKeys() {
        return $this->priKeys;
    }

    /**
     * @return array
     */
    public function getDataTypes() {
        return $this->dataTypes;
    }

    /**
     * @return array
     */
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
        $this->reset();
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
     *
     */
    protected function reset() {
        $this->hasError = false;
        $this->isBuilt = false;
        foreach($this->properties as $key => $property) {
            if ($this->isSet($key)) {
                //unset($this->{$key});
                $this->{$key} = null;
            }
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
     * @return bool
     */
    public function isSet($name) {
        return $this->has($name) && isset($this->{$name});
        
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name) {
        if ($this->isSet($name)) {
            return $this->$name;
        } elseif ($this->has($name)){
            return null;
        } else {
            throw new \Exception($name . ': Property not existing or not accessible or not set yet', 500);
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

    /**
    * save and persist changes before destroy the objectnif autosave is enabled
    */
    public function __destruct() {
        $this->save();
    }

    /**
     * @return bool
     */
    public function isValid(): bool {

        //TODO: Entity: update the logic that validating the entity based on primary keys having valid values and other criterias
        return !$this->hasError && $this->isBuilt;
    }

    /**
     * @return array
     */
    public function buildParams() {
        $result =array();
        foreach($this->properties as $key => $value) {
            if(!$this->isSet($key)) {
                continue;
            }
            if ($this->existing && $this->ableTo($key, 'update')) {
                $result[$key] = $this->{$key};
            } elseif (!$this->existing && $this->ableTo($key, 'insert')) {
                $result[$key] = $this->{$key};
            } elseif ($this->existing && $this->ableTo($key, 'delete')) {
                // TODO: Entity: implementation that the entity can be built for deletion
            }
        }
        return $result;
    }

    /**
     * @param Mapper|null $mapper
     * @return $this
     * @throws \Exception
     */
    public function save(Mapper &$mapper = null) {
        if (is_null($mapper) ) {
            $mapper = $this->mapper;
        }
    
        $this->fillDefaultValues();
        if ($this->isValid() && $this->arePropertiesValid()) {
            $mapper->addCache($this->existing? Mapper::TABLE_COMMITS_UPDATES : Mapper::TABLE_COMMITS_INSERTS, 
                                $this->buildParams(), $this->mandatoryPriKeys);
                                
        } else {
            throw new \Exception('Properties are invalid', 500);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function arePropertiesValid() {
        $this->fillDefaultValues();

        foreach($this->properties as $key => $value) {
            if ((!$this->isSet($key) && in_array($key, $this->notNullProperties)) ||
                ($this->isSet($key) && gettype($this->{$key}) !== $this->dataTypes[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     */
    protected function fillDefaultValues(){
        // TODO: Entity: add default values if value in absence and need default values
    }

    /**
     * Get parsed data types for all properties
     * @return array
     */
    protected function parsePropertyDataTypes() {
        return DBTypes::getDataTypes($this->properties);
    }

    /**
     * get the parsed non null properties
     * @return array
     */
    protected function parseNotNullProperties() {
        return DBTypes::getNotNullValues($this->properties);
    }

    /**
     * get the parsed primary keys in a list
     * @return array
     */
    protected function parsePrimaryKeys() {
        return DBTypes::getPrimaryKeys($this->properties);
    }

    /**
     * @return array
     */
    protected function parseMandatoryPrimaryKeys() {
        return DBTypes::getMandatoryPrimaryKeys($this->priKeys, $this->properties);
    }

    /**
     * @return Mapper|null
     */
    public function getMapper() {
        return $this->mapper;
    }

}