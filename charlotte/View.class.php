<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 6/14/2016
 * Time: 12:16 PM
 */
class View
{
    protected $variables = array();
    protected $_controller;
    protected $_action;
    function __construct($controller, $action)
    {
        $this->_controller = $controller;
        $this->_action = $action;
    }

    /** assign variables **/
    function assign($name, $value)
    {
        $this->variables[$name] = $value;
    }

    /** render views **/
    function render()
    {
        extract($this->variables);
        $defaultHeader = APP_PATH . 'app/views/header.php';
        $defaultFooter = APP_PATH . 'app/views/footer.php';
        $controllerHeader = APP_PATH . 'app/views/' . $this->_controller . '/header.php';
        $controllerFooter = APP_PATH . 'app/views/' . $this->_controller . '/footer.php';

        // header file
        if (file_exists($controllerHeader)) {
            include ($controllerHeader);
        } else {
            include ($defaultHeader);
        }
        // content file
        include (APP_PATH . 'app/views/' . $this->_controller . '/' . $this->_action . '.php');

        // footer file
        if (file_exists($controllerFooter)) {
            include ($controllerFooter);
        } else {
            include ($defaultFooter);
        }
    }
}