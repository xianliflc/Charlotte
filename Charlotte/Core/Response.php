<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/12/17
 * Time: 10:24 PM
 */

namespace Charlotte\Core;


class Response
{

    public function __construct($data)
    {
        $this->data = is_null($data) ? array('error' => true, 'message' => 'null response') : $data;
        $this->response = array();
        $this->dataType = gettype($this->data);

    }

    public function setHeader($header = 'json') {
        switch ($header) {
            case 'json':
                header('Content-Type: application/json');
                break;
            case 'html':
                header('Content-Type: text/html');
                break;
            case 'xml':
                header('Content-Type: text/html');
                break;
            default:
                header('Content-Type: application/json');
        }
    }

    public function buildResponse () {

        if ($this->hasError()) {
            return array('Error'=> true, 'ErrorMessage'=>$this->get('message'));
        }
        else {
            return $this->data;
        }
    }

    public function process () {
//        if (isset($this->options['type']) && ($this->options['type'] === 'json' || $this->options['type'] === 'xml')) {
//            $this->setHeader($this->options['type']);
//        }
//        else {
            $this->setHeader();
//        }
        $this->response = $this->buildResponse();
        echo json_encode($this->response);
    }

    public function hasError() {
        return array_key_exists('error', $this->data);
    }

    public function get($key) {
        return $this->data[$key];
    }

}
