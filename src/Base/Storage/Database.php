<?php

namespace Base\Storage;

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

    public function execute($query, $params)
    {
        $stmt = $this->getConnection()->prepare($query);
        foreach($params as $k => $v) {
            $stmt->bindValue(is_numeric($k) ? $k+1 : $k, $v);
        }
        $stmt->execute();
        return $stmt;
    }

    public function fetchRows($query, $params)
    {
        echo $query . PHP_EOL;
        return $this->execute($query, $params)->fetchAll();
    }
}