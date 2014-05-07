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
        $this->beginTransaction();
    }

    public function isPostgres()
    {
        return $this->getDatabasePlatform()->getName() == 'postgresql';
    }

    public function isOracle()
    {
        return $this->getDatabasePlatform()->getName() == 'oracle';
    }

    public function fetchNextvalFromSequence($sq_name)
    {
        if ($this->isOracle()) {
            $query = "select $sq_name.nextval from dual";
        } elseif ($this->isPostgres()) {
            $query = "select nextval('$sq_name')";
        } else {
            $platform_name = $this->getDatabasePlatform()->getName();
            throw new \Exception("Can't get nextval for DB type: $platform_name");
        }
        return $this->fetchColumn($query);
    }

    public function fetchNow()
    {
        if ($this->isOracle()) {
            $query = "select sysdate from dual";
        } elseif ($this->isPostgres()) {
            $query = "select to_char(clock_timestamp(), 'YYYY-MM-DD HH24:MI:SS')";
        } else {
            $platform_name = $this->getDatabasePlatform()->getName();
            throw new \Exception("Can't get nextval for DB type: $platform_name");
        }
        $now = $this->fetchColumn($query);
        return $now;
    }

    public function disableConstraints()
    {
        $this->executeQuery("SET CONSTRAINTS ALL DEFERRED");
    }

    public function enableConstraints()
    {
        $this->executeQuery("SET CONSTRAINTS ALL IMMEDIATE");
    }

}