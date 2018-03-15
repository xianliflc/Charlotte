<?php

namespace Charlotte\Services;

class Encryption {

    private static $instance = null;

    /**
     * method
     */
    private $encryption_method;

    /**
     * key
     */
    private $secret_key;

    /**
     * vector
     */
    private $secret_iv;

    private $secret_iv_size;

    /**
     * encryption mode
     */
    private $mode;

    private function __construct($encryption_method, $secret_key, $mode, $secret_iv, $secret_iv_size) {

        $this->encryption_method = $encryption_method;
        $this->secret_key = $secret_key;
        $this->secret_iv = $secret_iv;
        $this->mode = $mode;
        $this->secret_iv_size = $secret_iv_size;
    }

    public static function getInstance($encryption_method = "AES-256-CBC", $secret_key = 'test_key', $mode = 'sha256', $secret_iv = '0000000000000000', $secret_iv_size = 16) {

        if (self::$instance === null) {
            self::$instance = new Encryption($encryption_method, $secret_key, $mode, $secret_iv, $secret_iv_size);
        }
        return self::$instance;
    }

    /**
     * encrypt the given string
     */
    public function encrypt($string) {
        $output = false;

        // hash
        $key = hash($this->mode, $this->secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash($this->mode, $this->secret_iv), 0, $this->secret_iv_size);
        $output = openssl_encrypt($string, $this->encryption_method, $key, 0, $iv);
        $output = base64_encode($output);
        return $output;
    }


    /**
     * decrypt the given string
     */
    public function decrypt($string) {
        $output = false;

        // hash
        $key = hash($this->mode, $this->secret_key);

        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash($this->mode, $this->secret_iv), 0, $this->secret_iv_size);
        $output = openssl_decrypt(base64_decode($string), $this->encryption_method, $key, 0, $iv);
        return $output;
    }


}