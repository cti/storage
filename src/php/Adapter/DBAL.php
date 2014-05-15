<?php

namespace Cti\Storage\Adapter;

use \Doctrine\DBAL\Portability\Connection;


class DBAL extends Connection {

    protected $user;
    protected $password;

    public function __construct($config)
    {
        // keys must be lowercase
        $config['portability'] = Connection::PORTABILITY_FIX_CASE;
        $config['fetch_case'] = \PDO::CASE_LOWER;

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
        $this->initSession();
        $this->beginTransaction();
    }

    /**
     * Inits session params of database
     * @throws \Doctrine\DBAL\DBALException
     */
    public function initSession()
    {
        if ($this->isOracle()) {
            $this->executeQuery("alter session set nls_date_format='YYYY-MM-DD hh24:mi:ss'");
        }

    }

    public function isPostgres()
    {
        return $this->getDatabasePlatform()->getName() == 'postgresql';
    }

    public function isOracle()
    {
        return $this->getDatabasePlatform()->getName() == 'oracle';
    }

    public function isSQLite()
    {
        return $this->getDatabasePlatform()->getName() == 'sqlite';
    }

    public function fetchNextvalFromSequence($sq_name)
    {
        if ($this->isOracle()) {
            $query = "select $sq_name.nextval from dual";
        } elseif ($this->isPostgres()) {
            $query = "select nextval('$sq_name')";
        } elseif ($this->isSQLite()) {
            //sqlite has no sequences. get max(id) + 1 from table
            $table_name = str_replace('sq_', '', $sq_name);
            $id_field = 'id_' . $table_name;
            $query = "select max($id_field) from $table_name";
            $nextval = $this->fetchColumn($query);
            return (!$nextval ? 1 : $nextval + 1);
        } else {
            $platform_name = $this->getDatabasePlatform()->getName();
            throw new \Exception("Can't get nextval for DB type: $platform_name");
        }
        return $this->fetchColumn($query);
    }

    public function fetchNow()
    {
        if(!isset($this->now)) {
            if ($this->isOracle()) {
                $query = "select sysdate from dual";
            } elseif ($this->isPostgres()) {
                $query = "select to_char(clock_timestamp(), 'YYYY-MM-DD HH24:MI:SS')";
            } elseif ($this->isSQLite()) {
                $query = "select date('now')";
            } else {
                $platform_name = $this->getDatabasePlatform()->getName();
                throw new \Exception("Can't get nextval for DB type: $platform_name");
            }
            $this->now = $this->fetchColumn($query);
        }
        return $this->now;
    }

}