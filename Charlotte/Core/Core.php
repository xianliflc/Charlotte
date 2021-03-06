<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/12/17
 * Time: 5:52 PM
 */

namespace Charlotte\Core;


use Charlotte\Core\Controller;
use Charlotte\Http\Request;
use Charlotte\Log\Logger;
use Charlotte\Services\ServiceContainer;

class Core
{
    private static $instance = null;

    private $routes;
    private $config;
    private $request;
    private $logger;
    private $service_container;

    const version = '0.0.10 - alpha';

    private function __construct()
    {
        $this->logger = Logger::getInstance();
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
    function run( ServiceContainer $service_container)
    {
        $this->service_container = $service_container;
        $this->setReporting();
        $this->removeMagicQuotes();
        $this->request = new Request($_GET, json_decode(stripSlashes(file_get_contents("php://input")), true), $_COOKIE, $_SERVER, $_ENV);
        $this->unregisterGlobals();
        return $this->Route();
    }

    /**
     * Load config files
     * @throws \Exception
     */
    public function getConfigs() {
        try {
            
            $this->routes = new Config(ROUTES);
            $this->config = new Config(CONFIG);
            if (ENV !== 'prod' || ENV !== 'production') {
                $over_write_path = APP_PATH . '../config/environments/' . strtolower(ENV). '.json';

                if (!file_exists($over_write_path)) {
                    // TODO: add logic to notice that the config file under certain environment does not exist 
                } else {
                    //$config_overrwite = json_decode(file_get_contents(APP_PATH . '../config/environments/' . strtolower(ENV). '.json'), true);
                    $this->config = new Config(CONFIG, APP_PATH . '../config/environments/' . strtolower(ENV). '.json');
                }

            }

        } catch (\Exception $e) {
            // TODO: if config file is not parsed successfully, we need to do some work.
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
        $route_info = array();

        if (!is_null($pathName)) {
            foreach ($this->routes->getData() as $name => $route){

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
                    $route_info = $route;
                    $route_info['name'] = $name;
                    break;
                }
            }
        }

        if ($found_controller === false) {

            $controllerName = DEFAULT_CONTROLLER;
            $action = 'index';
        }

        $controller = $controller_prefix . $package . $controllerName . 'Controller';

        $request = array(
            'action'    => $action,
            'request'    => $this->request
        );

        $this->service_container->build();

        $dependencies = array(
            'services' => $this->service_container,
            'config' => $this->config,
            'route' => $route_info,
            'routes' => $this->routes
        );

        if (array_key_exists('auto_response', $this->config->get('environment')) && $this->config->get('environment->auto_response') === false){
            $auto_response = false;
        } else {
            $auto_response = true;
        }

        if ( class_exists ( $controller ) === true) {
           
            if ($auto_response === false) {
                $response = (new $controller($request, $dependencies))->getManualResponse();
                return $response;
            } else {
                $response = new $controller($request, $dependencies);
            }
        } else {
            
            if ($auto_response === false) {
                $response = (new Controller($request, $dependencies))->getManualResponse();
                return $response;
            } else {
                $response = new Controller($request, $dependencies);
            }
        }
    }

    /**
     * delete invalid chars
     */
    function removeMagicQuotes()
    {

        if ( get_magic_quotes_gpc()) {
            $_GET = $this->stripSlashesDeep($_GET );
            $_POST = $this->stripSlashesDeep($_POST );
            $_COOKIE = $this->stripSlashesDeep($_COOKIE);
            $_SESSION = $this->stripSlashesDeep($_SESSION);
        }
    }

    /**
     * remove globals
     */
    function unregisterGlobals()
    {
        if (array_key_exists('unregister_globals', $this->config->get('environment')) && $this->config->get('environment->unregister_globals') === true) {
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
        //TODO: CORE: implementation of reporting and logger
    }
}
