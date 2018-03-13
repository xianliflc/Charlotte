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
    public function getService($key)
    {
        if (isset($this->container[$key])) {
            return $this->container[$key];
        } else {
            return false;
        }
    }

    /**
     * @param $key
     * @param $value
     * @throws \Exception
     */
    public function setService($key, $value)
    {
        if (!is_object($value)) {
            throw new \Exception("Service must be object");
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

    public function get($key)
    {
        //TODO: implementation
    }

    public function set($key, $value)
    {
        //TODO: implementation
    }

}