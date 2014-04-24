<?php

ini_set('display_errors', 'on');
error_reporting(E_ALL);

$loader = include __DIR__.'/vendor/autoload.php';

$loader->add("Storage\\", __DIR__.'/tests/build/php');

/**
 * @return \Cti\Core\Application
 */
function getApplication() {

    static $application;

    if(!$application) {
        $config =  implode(DIRECTORY_SEPARATOR, array(__DIR__, 'tests', 'resources', 'php', 'config.php'));
        $application = Cti\Core\Application::create($config);
        $application->extend('Cti\Storage\Extension');
    }

    return $application;
}