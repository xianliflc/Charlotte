<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 6/14/2016
 * Time: 11:53 AM
 */
use Charlotte\Core\Core;
// config files
require APP_PATH . 'config/config.php';


// init consts
defined('FRAME_PATH') or define('FRAME_PATH', __DIR__.'/');
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');
defined('APP_DEBUG') or define('APP_DEBUG', false);
defined('CONFIG_PATH') or define('CONFIG_PATH', APP_PATH.'config/');
defined('RUNTIME_PATH') or define('RUNTIME_PATH', APP_PATH.'runtime/');

// components class files
const EXT = '.class.php';

// interface files
const INFC = '.interface.php';

// core files
require FRAME_PATH . 'Core/Core.php';

// init core class
$charlotte = new Core();
$charlotte->run();
