<?php

namespace Storage;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/**
 * Database
 * @package Storage
 */
class Database
{
    /**
     * @var Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @param array $config doctrine dbal driver manager configuration
     */
    function __construct($config)
    {
        $this->connection = DriverManager::getConnection($config);
    }

    /**
     * @return Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }
}