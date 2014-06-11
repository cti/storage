<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);
define("TEST_ENVIRONMENT", true);

$loader = include __DIR__.'/vendor/autoload.php';

$loader->add("Storage\\", __DIR__.'/tests/build/php');
$loader->add("Build\\", __DIR__.'/tests/build/php');
$loader->add("Model\\", __DIR__.'/tests/src/php');
$loader->add("Repository\\", __DIR__.'/tests/src/php');

include(__DIR__.'/tests/src/php/DatabaseManager.php');

/**
 * @return Build\Application
 */
function getApplication() {

    static $application;

    if(!$application) {
        $factory = \Cti\Core\Application\Factory::create(__DIR__.'/tests/');
        $application = $factory->getApplication();
    }

    return $application;
}