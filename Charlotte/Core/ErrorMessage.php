<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/14/17
 * Time: 4:41 PM
 */

namespace Charlotte\Core;


class ErrorMessage
{

    public static function NotFound ($message) {

        return array (
            'Error'         => true,
            'ErrorMessage'  => $message . ' Not Found.'
        );
    }

    public static function MissingInput ($message) {

        return array (
            'Error'         => true,
            'ErrorMessage'  => 'Missing Input: ' . $message
        );
    }

    public static function NotAvailable ($message) {

        return array (
            'Error'         => true,
            'ErrorMessage'  => 'The ' . $message . ' You are looking for is not available'
        );
    }

    public static function GeneralError ($message) {

        return array (
            'Error'         => true,
            'ErrorMessage'  => (string)$message
        );
    }


//    public static function InvalidInput ($message) {
//
//        return array (
//            'Error'         => true,
//            'ErrorMessage'  =>  $message . ' Not Found.'
//        );
//    }
}