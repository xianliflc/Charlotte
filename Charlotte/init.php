<?php
/**
 * Created by PhpStorm.
 * User: xiali
 * Date: 8/15/17
 * Time: 11:08 AM
 */

/**
 * Autoload all needed classes
 * @param $class
 */
function loadClass($class)
{
    $trusted_dirs = getTrustedDirs();
    $allowed_packages = getAllowedPackages();
    $class = preg_replace("/\\\\/", "/", $class);
    $included_files = get_included_files();

    if (file_exists($class.'.php')) {
        include_once $class.'.php';
    } else if(file_exists('__/' . $class . '.php')) {
        // include private libs
        include_once '__/' . $class . '.php';
    } else {
        $flag = false;
        foreach ($trusted_dirs as $dir) {
            $path =  $dir . $class . '.php';
            if( in_array($path, $included_files)){
                $flag = true;
                break;
            }
            elseif ($dir === APP.'Controllers/' || $dir === APP.'Containers/'){
                foreach ($allowed_packages as $allowed_package) {
                    $new_path = $dir . $allowed_package . '/' . $class . '.php';
                    if (file_exists($new_path)) {
                        $flag = true;
                        include_once $new_path;
                        break;
                    }
                }
            }
            elseif (file_exists($path)) {
                $flag = true;
                include_once $path;
            }
        }
        if ( $flag === false) {
            // $class . " does not exist. Please check again"
            $response = new \Charlotte\Core\Response(array('error'=>true, 'message'=>"Resource Not Found"), 404);
            $response->process();
        }
    }
}

function getTrustedDirs() {
    return array(
        'Charlotte/Core/'
    );
}

function getAllowedPackages() {
    return array(
        'Time'
    );

}

spl_autoload_register('loadClass');