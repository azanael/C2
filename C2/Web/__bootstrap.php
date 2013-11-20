<?php
/**
 * C2 Bootstrap.
 */

/*
 * Include Paths.
 */
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/../../'));

/*
 * Definitions.
 */
define('C2_BASE_DIR', realpath(__DIR__ . '/../'));


/*
 * Load Configs.
 */
require_once 'Config/c2.ini.php';
require_once 'Config/c2.db.ini.php';

/*
 * Load Exceptions.
 */
require_once 'Exception/C2_Exception.class.php';

/*
 * Set Logger.
 */
C2_Logger::init($path)

/*
 * Dispatch Page Controller.
 */
require_once 'Fundamental/C2_Dispatcher.class.php';
$dispatcher = new C2_Dispatcher();
try {
    $dispatcher->dispatch();
} catch (Exception $e) {
    $dispatcher->dispatch(404, 'ErrorController');
}

exit;