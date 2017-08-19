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

    public function __construct($get = array(), $post = array(), $cookies = array(), $others = array())
    {
        $this->get = $get;
        $this->post = $post;
        $this->cookies = $cookies;
        $this->others = $others;
    }

    // set function now only available to get and post params
    public function set($key, $value, $property = 'get') {
        if ($property === 'get' || $property === 'post') {
            $this->{$property}[$key] = $value;
        }
    }

    public function get($key, $property = 'get') {
        if( $this->has($key, $property)) {
            return $this->has($key, $property);
        }
        else {
            return false;
        }
    }

    public function has($key, $property = 'get') {
        if ($property === 'get' || $property === 'post' || $property === 'cookeis' || $property === 'others') {
            return isset($this->{$property}[$key]);
        }
        return false;
    }

    public function getAll ($property){
        if ($property === 'get' || $property === 'post' || $property === 'cookeis' || $property === 'others' ) {
            return $this->{$property};
        }
        else {
            return false;
        }
    }

}
