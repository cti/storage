<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);

$loader = include __DIR__.'/vendor/autoload.php';

$loader->add("Storage\\", __DIR__.'/tests/build/php');

/**
 * @return \Build\Application
 */
function getApplication() {

    static $application;

    if(!$application) {
        $factory = \Cti\Core\Application\Factory::create(__DIR__.'/tests/');
        $application = $factory->getApplication();
    }

    return $application;
}