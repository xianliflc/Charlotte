<?php

namespace Charlotte\Core;

class Config {

    private $data;
    //private static $instance = null;

    public function __construct(string $data = '', string $overwrite = '')
    {
        if ($overwrite !== '') {
            $this->data = $this->overWriteConfig(json_decode(file_get_contents($data), true), json_decode(file_get_contents($overwrite), true));
        } else {
            $this->data = json_decode(file_get_contents($data), true);
        }
        
    }

    // public static function getInstance(string $data = '', string $overwrite = '') {
    //     if (is_null(self::$instance)) {
    //         self::$instance = new Config($data, $overwrite);
    //     }
    //     return self::$instance;
    // }

    /**
     * Overwrite config files based on different environments 
     * @param array|mixed $target
     * @param array|mixed $overwrite
     * @return array|mixed $result
     */
    private function overWriteConfig($target, $overwrite) {
        $result = $target;
        if (gettype($target) !== 'array') {
            return $overwrite;
        }

        foreach ($overwrite as $key => $value) {
            if (!array_key_exists($key, $result)) {
                $result[$key] = $value;
            } elseif (array_key_exists($key, $result) && $result[$key] !== $value) {
                $result[$key] = $this->overWriteConfig($result[$key], $value);
            } else {
                continue;
            }
        }
        return $result;
    }

    /**
     * @param string $string
     * @param null $default
     * @return mixed|null
     */
    public function get(string $string, $default = null) {
        $path = explode('->', trim($string));
        $result = $this->data;
        foreach($path as $value) {
            $value = trim($value);
            if ($value === '') {
                break;
            }
            if (!is_array($result) || (is_array($result) && !array_key_exists($value, $result))) {
                //throw new \Exception('config: ' . $string . ' does not exist.', 500);
                return $default;
            }
            $result = $result[$value];
        }

        return $result;
    }


    /**
     * @param string $string
     * @return bool
     */
    public function has(string $string) {
        $path = explode('->', trim($string));
        $result = $this->data;
        foreach($path as $value) {
            $value = trim($value);
            if ($value === '') {
                break;
            }
            if (!is_array($result) || (is_array($result) && !array_key_exists($value, $result))) {
                //throw new \Exception('config: ' . $string . ' does not exist.', 500);
                return false;
            }
            $result = $result[$value];
        }
        return true;
    }

    public function getData() {
        return $this->data;
    }
}