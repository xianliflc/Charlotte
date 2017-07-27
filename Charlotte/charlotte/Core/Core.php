<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 6/14/2016
 * Time: 11:59 AM
 */

namespace Charlotte\Core;

class Core
{
    // run the code
    function run()
    {
        spl_autoload_register(array($this, 'loadClass'));
        $this->setReporting();
        $this->removeMagicQuotes();
        $this->unregisterGlobals();
        $this->Route();
    }
    // router
    function Route()
    {
        $controllerName = 'Index';
        $action = 'index';
        if (!empty($_GET['url'])) {
            $url = $_GET['url'];
            $urlArray = explode('/', $url);

            // get name of controller
            $controllerName = ucfirst($urlArray[0]);

            // get name of action
            array_shift($urlArray);
            $action = empty($urlArray[0]) ? 'index' : $urlArray[0];

            //get parameters in URL
            array_shift($urlArray);
            $queryString = empty($urlArray) ? array() : $urlArray;
        }
        // process when enpty request comming in
        $queryString  = empty($queryString) ? array() : $queryString;
        // init controller
        $controller = $controllerName . 'Controller';
        $dispatch = new $controller($controllerName, $action);
        // if controller and action exist, call and run
        if ((int)method_exists($controller, $action)) {
            call_user_func_array(array($dispatch, $action), $queryString);
        } else {
            exit($controller . "function ". $action . " does not exist in controller " . $controller);
        }
    }
    // check dev env
    function setReporting()
    {
        if (APP_DEBUG === true) {
            error_reporting(E_ALL);
            ini_set('display_errors','On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors','Off');
            ini_set('log_errors', 'On');
            ini_set('error_log', RUNTIME_PATH. 'logs/error.log');
        }
    }
    // delete invalid chars
    function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value);
        return $value;
    }
    // delete invalid chars
    function removeMagicQuotes()
    {
        if ( get_magic_quotes_gpc()) {
            $_GET = stripSlashesDeep($_GET );
            $_POST = stripSlashesDeep($_POST );
            $_COOKIE = stripSlashesDeep($_COOKIE);
            $_SESSION = stripSlashesDeep($_SESSION);
        }
    }
    // remove globals
    function unregisterGlobals()
    {
        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                foreach ($GLOBALS[$value] as $key => $var) {
                    if ($var === $GLOBALS[$key]) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }
    // autoload controllers and models
    static function loadClass($class)
    {
        $class = preg_replace("/\\\\/", "/", $class);
        $frameworks = FRAME_PATH . $class . EXT;
        $frameworks_interface = FRAME_PATH . $class . INFC;
        $controllers = APP_PATH . 'app/controllers/' . $class . EXT;
        $controllers_interface = APP_PATH . 'app/controllers/' . $class . INFC;
        $models = APP_PATH . 'app/models/' . $class . EXT;
        $models_interface = APP_PATH . 'app/models/' . $class . INFC;
        if (file_exists($frameworks)) {
            // load core class
            include_once $frameworks;
        } elseif (file_exists($frameworks_interface)) {
            // load core class
            include_once $frameworks_interface;
        } elseif (file_exists($controllers)) {
            // load controllers
            include_once $controllers;
        } elseif (file_exists($controllers_interface)) {
            // load controllers
            include_once $controllers_interface;
        } elseif (file_exists($models)) {
            //load models
            include $models;
        }  elseif (file_exists($models_interface)) {
            //load models
            include_once $models_interface;
        }else {
            /* error */
        }
    }
}