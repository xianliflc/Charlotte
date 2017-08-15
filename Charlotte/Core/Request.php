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

    public function __construct($get = array(), $post = array())
    {
        $this->get = $get;
        $this->post = $post;
    }

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
        if ($property === 'get' || $property === 'post') {
            return isset($this->{$property}[$key]);
        }
        return false;
    }

    public function getAll ($property){
        if($property === 'get' || $property === 'post' ) {
            return $this->{$property};
        }
        else {
            return false;
        }
    }

}