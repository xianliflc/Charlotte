<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 3/11/18
 * Time: 5:52 PM
 */
namespace Charlotte\Services;

use Charlotte\Core\Defination;

class Service {

    /**
     * defination of the class
     */
    private $defination;

    /**
     * whether the class has an instance
     */
    private $is_initilized;

    /**
     * publicly available properties
     */
    public $properties;

    /**
     * the instance of the class
     */
    private $object;


    public function __construct(Defination $def)
    {
        $this->defination = $def;  
        $this->is_initilized = false;  
        $this->object = null;
        $this->properties = array();
    }

    /**
     * get the instance of the class
     */
    public function getObj() {
        if (is_null($this->object)) {
            $this->object = $this->defination->getObj();
        }
        return $this->object;
    }

    /**
     * set parameters of the inner object
     */
    public function setParameter($key, $value) {
        $this->defination->setParameter($key, $value);
    }

    /**
     * build the object based on defination of class
     */
    public function build () {
        $this->defination->build();
        $this->is_initilized = true;
    }

    /**
     * check whether the object is initialzed
     */
    public function isInitialized() {
        return $this->is_initilized === true;
    }

    /**
     * wrapper of magical function call
     */
    public function __call($name, $arguements) {

        if ($this->isInitialized() && method_exists($this->getObj(), $name) 
        &&  is_callable(array($this->getObj(), $name))) {
           return ($this->getObj())->{$name}(...$arguements);
        } else {
           return  $this->{$name}(...$arguements);
        }
        
    }

    /**
     * wrapper of magical getter
     */
    public function __get($name) {

        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        } else if (array_key_exists($name, get_object_vars($this->getObj()))) {
            return $this->getObj()->{$name};
        } else {
            if($this->isStrict()) {
                throw new \Exception('Service/ Defination not initialized or property not found');
            } else {
                return null; 
            }
            
        }
    }


    /**
     * wrapper of magical setter
     */
    public function __set($name, $value) {

        if (array_key_exists($name, $this->properties)) {
            $this->properties[$name] = $value;
        } else if (array_key_exists($name, get_object_vars($this->getObj()))) {
            return $this->getObj()->{$name} = $value;
        } else {
            if($this->isStrict()) {
                throw new \Exception('Service/ Defination not initialized or property not found');
            } else {
                return false; 
            }
        }
    }


    /**
     * Check whether the inner object is in strict mode
     */
    public function isStrict() {
        return $this->getObj()->isStrict();
    }


}
