<?php

namespace Cti\Storage\Generator;

class Database {
    /**
     * @var \Doctrine\DBAL\Schema\Schema
     */
    private $toSchema;
    /**
     * @var \Doctrine\DBAL\Schema\Schema
     */
    private $fromSchema;
    private $testMode;

    /**
     * @inject
     * @var \Cti\Storage\Adapter\DBAL
     */
    private $oracle;

    public function setToSchema(\Doctrine\DBAL\Schema\Schema $toSchema) {
        $this->toSchema = $toSchema;
    }

    public function setFromSchema(\Doctrine\DBAL\Schema\Schema $fromSchema) {
        $this->fromSchema = $fromSchema;
    }

    /**
     * @param $value bool
     */
    public function setTestMode($value)
    {
        $this->testMode = $value;
    }

    /**
     * @return bool
     */
    public function isInTestMode()
    {
        return $this->testMode === true;
    }

    public function migrate()
    {
        if (empty($this->toSchema)) {
            throw new \Exception("To schema not defined in Generator\\Database");
        }
        if (empty($this->fromSchema)) {
            throw new \Exception("From schema not defined in Generator\\Database");
        }
        $sql = $this->fromSchema->getMigrateToSql($this->toSchema, $this->oracle->getDatabasePlatform());
        if (!$this->isInTestMode()) {
            foreach($sql as $query) {
                $this->oracle->executeQuery($query);
            }
        }
        return $sql;
    }
} 