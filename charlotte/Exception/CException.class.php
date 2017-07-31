<?php
namespace Charlotte\Exception;

use Charlotte\Interfaces\Exception\Exception as BaseException;

class CException extends \Exception implements BaseException
{
    public function __construct($message = null, $code = 0){
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }
        parent::__construct($message, $code);
    }
    
    
}
