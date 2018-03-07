<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/12/17
 * Time: 11:27 PM
 */

namespace Charlotte\Core;


class Request
{
    private $get;
    private $post;
    private $cookies;
    private $others;
    private $server;
    private $env;

    public function __construct($get = array(), $post = array(), $cookies = array(), $server = array(), $env = array(), $others = array())
    {
        $this->get = $get;
        $this->post = $post;
        $this->cookies = $cookies;
        $this->server = $server;
        $this->env = $env;
        $this->others = $others;
    }

    public function set($key, $value, $property = 'get') {
        if ($property === 'get' || $property === 'post') {
            $this->{$property}[$key] = $value;
        }
    }

    public function get($key, $property = 'get', $default = false) {
        if( $this->has($key, $property)) {
            return $this->{$property}[$key];
        }
        else {
            return $default;
        }
    }

    public function has($key, $property = 'get') {
        if (in_array($property, ['get', 'post', 'cookies', 'env', 'others', 'server'])) {
            return isset($this->{$property}[$key]);
        }
        return false;
    }

    public function getAll ($property){
        if (in_array($property, ['get', 'post', 'cookies', 'env', 'others', 'server']) ) {
            return $this->{$property};
        }
        else {
            return false;
        }
    }

}
