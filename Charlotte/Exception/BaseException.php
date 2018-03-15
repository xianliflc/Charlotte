<?php

namespace Charlotte\Exception;

abstract class BaseException extends \Exception{

    public function __construct($message = "Charlotte Base Exception", $code = null, \Exception $ex = null)
    {
        parent::__construct($message, $code, $ex);

        if (PHP_SAPI === 'cli') {

        } else {

        }
    }


    public function handleCli(\Exception $e) {

    }

    abstract protected function handle(\Exception $e);
}