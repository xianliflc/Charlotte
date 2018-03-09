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

    public function __construct($data, $code = 200, $type = 'json')
    {
        $this->data = is_null($data) ?
            array('error' => true, 'message' => 'null response') :
            $data;
        $this->code = $code;
        $this->response = array();
        $this->dataType = gettype($this->data);

    }

    public function setHeader($header = 'json', $code = 200) {
        switch ($header) {
            case 'json':
                header('Content-Type: application/json', true, $code);
                break;
            case 'html':
                header('Content-Type: text/html', true, $code);
                break;
            case 'xml':
                header('Content-Type: text/html', true, $code);
                break;
            default:
                header('Content-Type: application/json', true, $code);
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

    /**
     * Send the response back to client and terminate the app
     */
    public function process () {

        $this->setHeader($this->dataType, $this->code);
        $this->response = $this->buildResponse();
        echo json_encode($this->response);
        exit(0);
    }

    public function hasError() {
        return array_key_exists('error', $this->data);
    }

    public function get($key) {
        return $this->data[$key];
    }

}
