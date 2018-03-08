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
    private $config;
    private $request;

    const version = '0.0.4 - alpha';

    private function __construct()
    {
        $this->getConfigs();
    }

    /**
     * return the unique instance of Core
     * @return Core|null
     */
    public static function getInstance() {

        if (self::$instance === null) {
            self::$instance = new Core();
        }
        return self::$instance;
    }

    /**
     * Main process
     */
    function run()
    {
        $this->setReporting();
        $this->removeMagicQuotes();
        $this->request = new Request($_GET, json_decode(stripSlashes(file_get_contents("php://input")), true), $_COOKIE, $_SERVER, $_ENV);
        $this->unregisterGlobals();
        $this->Route();
    }

    /**
     * Load config files
     * @throws \Exception
     */
    public function getConfigs() {
        try {
            $this->routes = json_decode(file_get_contents(ROUTES), true);
            $this->config = json_decode(file_get_contents(CONFIG), true);
        } catch (\Exception $e) {
            // TODO: more logic before throwing out the exception
            throw $e;
        }
    }

    /**
     * Dispatch the request to its corresponding controller
     */
    public function Route()
    {

        $controllerName = 'Application';
        $action = 'index';
        if ($this->request->get('url')) {
            $pathName = $this->request->get('url');
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
                    $request_method = strtolower($this->request->get('REQUEST_METHOD','server'));
                    $allowed_methods = array_map('strtolower', $route['methods']);

                    if (!in_array($request_method, $allowed_methods)) {
                        throw new \Exception('method not allowed', 405);
                    }
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

            $dependencies = array(
                'config' => $this->config
            );

            if ( class_exists ( $controller ) === true) {
                $pispatch = new $controller($request, $dependencies);
            } else {
                $pispatch = new Controller($request, $dependencies);
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
            $_GET = $this->stripSlashesDeep($_GET );
            $_POST = $this->stripSlashesDeep($_POST );
            $_COOKIE = $this->stripSlashesDeep($_COOKIE);
            $_SESSION = $this->stripSlashesDeep($_SESSION);
        }
    }

    // remove globals
    function unregisterGlobals()
    {
        if (array_key_exists('unregister_globals', $this->config['environment']) && $this->config['environment']['unregister_globals'] === true) {
            $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
            foreach ($array as $value) {
                unset($GLOBALS[$value]);
            }
        }
    }

    /**
     * @param $value
     * @return array|string
     */
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
