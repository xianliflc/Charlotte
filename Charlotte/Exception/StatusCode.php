<?php

namespace Charlotte\Exception;

class StatusCode {

    public const STATUS_CODE = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );

    public const UNKNOWN_CODE = 999;

    public const UNKNOWN_DESCRIPTION = 'unknown status';


    protected static function getStatusHelper($data = 'unknown') {
        if( is_numeric($data) && array_key_exists((int)$data, self::STATUS_CODE)) {
            return array('status_code'=> (int)$data, 'status' => self::STATUS_CODE[(int)$data]);
        } else if (is_string($data) && $index = array_search($data, self::STATUS_CODE)) {
            return array('status_code'=> $index, 'status' => self::STATUS_CODE[$index]);
        }
        return array('status_code' => self::UNKNOWN_CODE, 'status' => self::UNKNOWN_DESCRIPTION);
    }

    public static function getStatus($data) {
        return static::getStatusHelper($data);
    }

    public static function hasStatus(int $code = 0) {
        return array_key_exists($code, self::STATUS_CODE);
    }
}