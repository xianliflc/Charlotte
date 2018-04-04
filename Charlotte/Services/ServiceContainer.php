<?php
/**
 * Created by PhpStorm.
 * @author  xiali
 * @date: 3/11/18
 * @time: 5:52 PM
 */
namespace Charlotte\Services;

use Charlotte\Core\Defination;
use Charlotte\Services\Service;

class ServiceContainer
{

    /**
     * container of services
     */
    private $container;

    /**
     * the singleton instance
     */
    private static $instance = null;

    private function __construct()
    {
        $this->container = array();
    }

    /**
     * Get the instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ServiceContainer();
        }
        return self::$instance;
    }


    /**
     * add service to the container
     * @param $name
     * @param \Charlotte\Services\Service $service
     */
    public function addService($name, Service $service)
    {
        $this->container[$name] = $service;
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public function getService(string $key, $default = false)
    {
        if ($this->hasService($key)) {
            return $this->container[$key];
        } else {
            return $default;
        }
    }

    /**
     * @param $key
     * @param $value
     * @throws \Exception
     */
    public function setService(string $key, $value)
    {
        if (!is_object($value)) {
            throw new \Exception("Service must be object", 500);
        }
        $this->container[$key] = $value;
    }

    /**
     * @throws \Exception
     */
    public function build()
    {
        foreach ($this->container as $service) {
            if (!$service instanceof Service) {
                throw new \Exception("Invalid service cannot be initialzed", 500);
            }
            $service->build();
        }
    }

    /**
    * @param string $key
    * return bool
    */
    public function hasService($key)
    {
        return isset($this->container[$key]) && is_object($this->container[$key]);
    }

}