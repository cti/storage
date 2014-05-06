<?php

namespace Cti\Storage\Adapter;


class DBAL extends \Doctrine\DBAL\Connection {

    protected $user;
    protected $password;

    public function __construct($config)
    {
        if ($config['driver'] == 'oracle') {
            $config['charset'] = 'AL32UTF8';
            $config['driver'] = 'oci8';
            $config['dbname'] = $config['tns'];
            unset($config['tns']);
            $driver = new \Doctrine\DBAL\Driver\OCI8\Driver();
        } elseif ($config['driver'] == 'sqlite') {
            $config['driver'] = 'pdo_sqlite';
            $driver = new \Doctrine\DBAL\Driver\PDOSqlite\Driver();
        } elseif ($config['driver'] == 'postgres') {
            $config['driver'] = 'pdo_pgsql';
            unset($config['driver']);
            $driver = new \Doctrine\DBAL\Driver\PDOPgSql\Driver();
        } else {
            throw new \Exception("Unknown driver \"" . $config['driver'] . "\" for database in config");
        }
        parent::__construct($config, $driver);
    }
}