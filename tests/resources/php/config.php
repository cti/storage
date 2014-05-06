<?php
return array(
    'Cti\Storage\Adapter\DBAL' => array(
        'user' => 'test',
        'password' => 'test',
        'memory' => true,
        'driver' => 'sqlite',
    ),
    'Cti\Core\Application\Generator' => array(
        'modules' => array(
            'Cti\Storage\Storage'
        )
    ),
    'Cti\Core\Module\Project' => array(
        'path' => dirname(dirname(__DIR__)),
    ),
    'Cti\Storage\Storage' => array(
        'prefix' => 'Cti\\Storage\\',

    )
);