<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/12/17
 * Time: 6:27 PM
 */

namespace Charlotte\Core;

use Charlotte\Http\Response;
use Charlotte\Core\ErrorMessage;

class Controller
{

    protected $request;
    private $action;
    private $manual_response;

    public function __construct($request, $dependencies)
    {
        $this->set('action', $request['action']);
        $this->set('request', $request['request']);
        foreach($dependencies as $key => $dependency) {
            $this->set($key, $dependency);
        }
        // $this->request = $request['request'];
        if (!isset($dependencies['route']['ignore_validation']) ||
            (isset($dependencies['route']['ignore_validation']) && $dependencies['route']['ignore_validation'] !== true)
        ) {
            $this->validate();
        }
        $this->manual_response = $this->run();
    }

    public function getManualResponse() {
        return $this->manual_response;
    }

    
    /**
     * @throws \Exception
     */
    protected function validate() {
        throw new \Exception('validation has to be implemented in each controller', 400);
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

    /**
     * Main entry
     */
    public function run() {

        if (isset($this->action )) {
            $action =  $this->get('action');

            if (array_key_exists('auto_response', $this->get('config')['environment']) && $this->get('config')['environment']['auto_response'] === false) {
                $auto_response = false;
            } else {
                $auto_response = true;
            }

            if ((int)method_exists($this, $action . 'Action')) {
                $response = new Response($this->{$action . 'Action'}());
                if ($auto_response === false) {
                    return $response;
                }
                $response->process();
            } else {
                $response = new Response($this->notFoundAction());
                if ($auto_response === false) {
                    return $response;
                }
                $response->process();
            }
        } else {
            $response = new Response($this->indexAction());
            if ($auto_response === false) {
                return $response;
            }
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