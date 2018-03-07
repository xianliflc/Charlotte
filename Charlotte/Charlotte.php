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
defined('APP') or define('APP', APP_PATH . 'app/');
defined('CONFIG') or define('CONFIG', APP_PATH . '../config/shared.json');
defined('ROUTES') or define('ROUTES', APP_PATH . '../config/routes.json');
defined('DEFAULT_CONTROLLER') or define('DEFAULT_CONTROLLER', 'Application');


defined('DB_HOST') or define('DB_HOST', '127.0.0.1');
defined('DB_USER') or define('DB_USER', 'test');
defined('DB_PASSWORD') or define('DB_PASSWORD', 'test');
defined('DB_NAME') or define('DB_NAME', 'test');
defined('DB_PORT') or define('DB_PORT', '3306');


$core = Core::getInstance();

$core->run();
