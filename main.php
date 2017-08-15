<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/15/17
 * Time: 11:11 AM
 */

defined('APP_PATH') or define('APP_PATH', __DIR__.'/');
defined('APP') or define('APP', APP_PATH . 'app/');
defined('CONFIG') or define('CONFIG', APP_PATH . 'config/config.php');
defined('ROUTES') or define('ROUTES', APP_PATH . 'config/routes.php');
defined('DEFAULT_CONTROLLER') or define('DEFAULT_CONTROLLER', 'Application');

include_once 'config/config.php';