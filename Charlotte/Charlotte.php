<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/15/17
 * Time: 11:05 AM
 */

use Charlotte\Core\Core;

include_once 'init.php';

defined('APP_PATH') or define('APP_PATH', __DIR__.'/');
defined('APP') or define('APP', APP_PATH . '../app/');
defined('CONFIG') or define('CONFIG', APP_PATH . '../config/shared.json');
defined('ROUTES') or define('ROUTES', APP_PATH . '../config/routes.json');
defined('DEFAULT_CONTROLLER') or define('DEFAULT_CONTROLLER', 'Application');


$core = Core::getInstance();

try {
    $core->run();
} catch (\Exception $e) {
    $response = new \Charlotte\Core\Response(array('error'=>true, 'message'=>$e->getMessage()), $e->getCode());
    $response->process();
}

