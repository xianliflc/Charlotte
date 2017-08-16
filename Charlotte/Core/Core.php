<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/12/17
 * Time: 5:52 PM
 */

namespace Charlotte\Core;


use Charlotte\Core\Controller;
use Charlotte\Core\Request;

class Core
{
    private static $instance = null;

    private $routes;
    private $request;

    private function __construct()
    {
        $this->getConfigs();
        $this->routes = routes();
        $this->request = new Request(array() , array());
    }

    public static function getInstance() {

        if (self::$instance === null) {
            self::$instance = new Core();
        }
        return self::$instance;
    }

    function run()
    {
        $this->setReporting();
        $this->removeMagicQuotes();
        $this->unregisterGlobals();
        $this->Route();
    }

    public function getConfigs() {
        include_once ROUTES;
    }

    public function Route()
    {

        $controllerName = 'Application';
        $action = 'index';
        if (!empty($_GET['url'])) {
            $url = $_GET['url'];
            $urlArray = explode('/', $url);
            $pathName = $urlArray[0];
            if ( is_null($pathName) ) {
                exit('Wrong Route');
            }
        }
        else {
            $pathName = null;
            $controllerName = DEFAULT_CONTROLLER;
            $action = 'index';
        }

        // init controller
        $controller_prefix = 'app\\Controllers\\';
        $package = '';
        $found_controller = false;
        if (!is_null($pathName)) {
            foreach ($this->routes as $route){
                if ($route['path'] === $pathName) {
                    $controllerName = $route['controller'];
                    $action = isset($route['action']) && !empty($route['action'])? $route['action'] : 'index';
                    $package = $route['package']. '\\';
                    $found_controller = true;
                    break;
                }
            }
        }

        if ($found_controller === false) {

            $controllerName = DEFAULT_CONTROLLER;
            $action = 'index';
        }

        $controller = $controller_prefix . $package . $controllerName . 'Controller';

        try{
            $request = array(
                'action'    => $action,
                'request'    => $this->request
            );
            if ( class_exists ( $controller ) === true) {
                $pispatch = new $controller($request);
            } else {
                $pispatch = new Controller($request);
            }
            

        }
        catch(\Exception $error) {
            exit($error->getMessage());
        }

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
        $this->request = new Request($_GET, json_decode(stripSlashes(file_get_contents("php://input")), true));

        if (ini_get('register_globals')) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                unset($GLOBALS[$value]);
            }
        }
    }

    // delete invalid chars
    function stripSlashesDeep($value)
    {
        $value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value);
        return $value;
    }

    /**
     * check dev env
     */
    function setReporting()
    {
        //TODO: implementation
    }
}