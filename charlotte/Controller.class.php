<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 6/14/2016
 * Time: 12:08 PM
 */
/**
 * Base Controller
 */
class Controller
{
    protected $_controller;
    protected $_action;
    protected $_view;

    // initialization of properties and models
    function __construct($controller, $action)
    {
        $this->_controller = $controller;
        $this->_action = $action;
        $this->_view = new View($controller, $action);
    }
    // assign variables
    function assign($name, $value)
    {
        $this->_view->assign($name, $value);
    }
    // render views
    function __destruct()
    {
        $this->_view->render();
    }
}
