<?php

use Cti\Core\Application;

$application = Application::create( __DIR__ . DIRECTORY_SEPARATOR . 'config.php');
$application->extend('Cti\Storage\Extension');
return $application;