<?php
return array(
    'Cti\Storage\Adapter\DBAL' => array(
        'user' => 'test',
        'password' => 'test',
        'host' => 'pg1',
        'port' => '5432',
        'dbname' => 'test',
        'driver' => 'postgres',
    ),
    'Cti\Core\Application\Generator' => array(
        'modules' => array(
            'storage' => 'Cti\Storage\Module'
        )
    ),
    'Cti\Core\Module\Project' => array(
        'path' => dirname(dirname(__DIR__)),
    ),
    'Cti\Storage\Storage' => array(
        'prefix' => 'Cti\\Storage\\',

    )
);