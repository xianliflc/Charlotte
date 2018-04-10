<?php

namespace Charlotte\Exception;

class ErrorCode extends StatusCode {

    public const INTERNAL_STATUS_CODE = array(
        600 => 'General Routes Error',
        700 => 'General ORM Error',
        800 => 'General Components Error',
        900 => 'General Error'
    );

    protected static function getStatusHelper($data = 'unknown') {
        $result = false;
        if( is_numeric($data) && array_key_exists((int)$data, self::INTERNAL_STATUS_CODE)) {
            return array('status_code'=> (int)$data, 'status' => self::INTERNAL_STATUS_CODE[(int)$data]);
        } else if (is_string($data) && $index = array_search($data, self::INTERNAL_STATUS_CODE)) {
            return array('status_code'=> $index, 'status' => self::INTERNAL_STATUS_CODE[$index]);
        } else if( is_numeric($data) && array_key_exists((int)$data, self::STATUS_CODE) ) {
            return array('status_code'=> (int)$data, 'status' => self::STATUS_CODE[(int)$data]);
        } else if (is_string($data) && $index = array_search($data, self::STATUS_CODE)) {
            return array('status_code'=> $index, 'status' => self::STATUS_CODE[$index]);
        }
        return array('status_code' => self::UNKNOWN_CODE, 'status' => self::UNKNOWN_DESCRIPTION);
    }

    public static function hasStatus(int $code = 0) {
        return array_key_exists($code, self::STATUS_CODE) || array_key_exists($code, self::INTERNAL_STATUS_CODE);
    }
}