<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/12/17
 * Time: 6:27 PM
 */

namespace Charlotte\Core;

use Charlotte\Core\Response;
use Charlotte\Core\ErrorMessage;

class Controller
{

    public function __construct($request)
    {
        $this->set('request', $request);

        $this->validate();
        return $this->run();
    }

    protected function validate() {

    }

    protected function set($key, $value) {
        $this->{$key} = $value;
    }

    protected function get($key) {
        return $this->has($key)? $this->{$key} : null;
    }

    protected function has($key) {
        return isset($this->{$key});
    }

    protected function getRequest() {
        return $this->get('request')['request'];
    }

    public function run() {

        if (isset($this->get('request')['action']) ) {
            $action =  $this->get('request')['action'];

            if ((int)method_exists($this, $action . 'Action')) {
                $response = new Response($this->{$action . 'Action'}());
                $response->process();
            } else {
                $response = new Response($this->notFoundAction());
                $response->process();
            }
        } else {
            $response = new Response($this->indexAction());
            $response->process();
        }
    }

    public function indexAction() {
        return $this->errorResponse('NotAvailable', 'Resources');
    }

    protected function notFoundAction () {
        return $this->errorResponse('NotAvailable', 'Action');
    }

    protected function required($params, $required = array()) {

        if (count($params) < count($required)) {
            return false;
        }
        else {
            foreach ( $required as $v) {
                if ( !array_key_exists($v, $params)) {
                    return false;
                }
                else if ( is_null($params[$v]) || empty( $params[$v])) {
                    return false;
                }
            }
            return true;
        }
    }

    protected function errorResponse($type, $message) {
        try {
            return ErrorMessage::{$type}($message);
        } catch (\Exception $e) {
            return array(
                'Error'=> true,
                'ErrorMessage' => $e->getMessage()
            );
        }

    }
}