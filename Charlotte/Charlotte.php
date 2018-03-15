<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/15/17
 * Time: 11:05 AM
 */

use Charlotte\Core\Core;
use Charlotte\Services\ServiceContainer;
use Charlotte\Services\Service;
use Charlotte\Core\Defination;
use Charlotte\Http\Response;

include_once 'init.php';

defined('APP_PATH') or define('APP_PATH', __DIR__.'/');
defined('APP') or define('APP', APP_PATH . '../app/');
defined('CONFIG') or define('CONFIG', APP_PATH . '../config/shared.json');
defined('ROUTES') or define('ROUTES', APP_PATH . '../config/routes.json');
defined('DEFAULT_CONTROLLER') or define('DEFAULT_CONTROLLER', 'Application');

if (isset($_SERVER['env'])) {
    defined('ENV') or define('ENV', $_SERVER['env']);
} else {
    defined('ENV') or define('ENV', 'dev');
}

if (!file_exists(ROUTES)) {
    throw new \Exception('No routes config found in app', 404);
}

if (!file_exists(CONFIG)) {
    throw new \Exception('No default config found in app', 404);
}



$core = Core::getInstance();

$service_contaienr = ServiceContainer::getInstance();

try {
    $service_contaienr->addService('test', new Service(new Defination('app\\Lib\\Service\\TestService', false, 123, 222)));
    $service_contaienr->addService('encryption', new Service(new Defination('Charlotte\\Services\\Encryption', false)));
    $response = $core->run($service_contaienr);

    $response->setContentType('json')->sendResponseHeaders()->finalize();
} catch (\Exception $e) {
    $response = new \Charlotte\Http\Response(array('error'=>true, 'message'=>$e->getMessage()), $e->getCode());
    $response->setContentType('json')->sendResponseHeaders()->finalize();
    //$response->process();
}

