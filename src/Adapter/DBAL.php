<?php

namespace Cti\Storage\Adapter;


class DBAL extends \Doctrine\DBAL\Connection {

    protected $user;
    protected $password;
    protected $tns;
    protected $driver;

    public function __construct($config)
    {
        $params = array(
            'user' => $config['user'],
            'password' => $config['password'],
            'dbname' => $config['tns'],
            'charset' => 'AL32UTF8',
        );
        if ($config['driver'] == 'oracle') {
            $params['driver'] = 'oci8';
            $driver = new \Doctrine\DBAL\Driver\OCI8\Driver();
        } else {
            throw new \Exception("Unknown driver for database in config");
        }
        parent::__construct($params, $driver);
    }
}