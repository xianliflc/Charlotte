<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/12/17
 * Time: 11:27 PM
 */

namespace Charlotte\Http;


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

    /**
     * get a certain value based on name, channel and default value
     */
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

    /**
     * Check the method of the current request
     * @param string $method
     * @return bool
     */
    public function isMethod ($method = '') {
        return $method === $this->get('REQUEST_METHOD','server');
    }

    /**
     * get the current request method
     */
    public function getMethod() {
        return $this->get('REQUEST_METHOD','server');
    }

    /**
     * get the current host
     */
    public function getHost() {
        return $this->get('HTTP_HOST', 'server');
    }

    /**
     * get the time of the request received
     */
    public function getRequestTime() {
        return $this->get('REQUEST_TIME', 'server');
    }

    /**
     * server port
     */
    public function getServerPort() {
        return $this->get('SERVER_PORT', 'server');
    }

    public function getRequestURI() {
        return $this->get('REQUEST_URI', 'server');
    }

    public function getPathInfo() {
        return $this->get('PATH_INFO', 'server');
    }

    public function getUserAgent() {
        return $this->get('HTTP_USER_AGENT', 'server');
    }

    public function isAjax() {
        return 0 === strcasecmp($this->get('HTTP_X_REQUESTED_WITH', 'server'), 'XMLHttpRequest');
    }

    /**
     * get the client ip address
     */
    public function getClientIPAdress() {
        static $ip = null;
        if (null === $ip) {
            if (empty($env_keys)) {
                $env_keys = array(
                    'HTTP_CLIENT_IP',
                    'HTTP_CF_CONNECTING_IP',
                    'HTTP_X_FORWARDED_FOR', 
                    'HTTP_X_FORWARDED',
                    'HTTP_X_CLUSTER_CLIENT_IP',
                    'HTTP_FORWARDED_FOR', 
                    'HTTP_FORWARDED',
                    'REMOTE_ADDR'
                );
            }

            $ip = '0.0.0.0';
            foreach ($env_keys as $env) {
                $env_info = $this->get($env, 'server');
                if ($env_info && !empty($env_info) && 0 !== strcasecmp($env_info, 'unknown')) {
                    $ips = explode(',', $env_info);
                    foreach ($ips as $ip) {
                        $ip = trim($ip);
                        $temp_ip = ip2long($ip);
                        if (false !== $temp_ip && $temp_ip !== -1) {                           
                            break 2;
                        }
                    }
                }
            }
        }

        return $ip;
    }

}
