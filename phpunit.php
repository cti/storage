<?php

$loader = include __DIR__.'/vendor/autoload.php';
$loader->register('Storage\\', implode(DIRECTORY_SEPARATOR, array(__DIR__, 'tests', 'build', 'php')));
