<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 3/11/18
 * Time: 5:52 PM
 */
namespace Charlotte\Core;

class Defination {

    /**
     * params of the class
     */
    private $params;

    /**
     * class name
     */
    private $class;

    /**
     * determine whether class
     */
    private $strict_mode;

    /**
     * initialized obj
     */
    private $obj;

    public function __construct($class = "\\stdClass", $strict_mode = false, ... $params)
    {
        $this->class = $class;
        $this->params = $params;
        $this->strict_mode = $strict_mode;
    }

    public function setParameter($key, $value) {
        $this->params[$key] = $value;
    }

    /**
     * build the obj based on the params
     */
    public function build() {
 
        // check if the class is singleton
        if (is_callable(array($this->class, 'getInstance'))) {
            $this->obj = $this->class::getInstance(...$this->params);
        }
        else {
            $this->obj = new $this->class(...$this->params);
        }

        // if the object is not initialized successfully, then do some handling
        if (! is_object($this->obj) || !class_exists($this->class, false)) {
            if ($this->isStrict()) {
                throw new \Exception("Resource Not Found: " . $this->class);
            } else {
                $this->obj = null;
            }
        }
    }

    /**
     * get the obj from outside
     */
    public function getObj() {
        return $this->obj;
    }

    /**
     * Check whether this object is in strict
     */
    public function isStrict() {
        return $this->strict_mode === true;
    }

     
}