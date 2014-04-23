<?php

namespace Cti\Storage\Converter;
/**
 * Class DBAL
 * Converts Cti\Storage\Schema to Doctrine Schema
 * @package Cti\Storage\Converter
 */
class DBAL {

    /**
     * @inject
     * @var \Cti\Core\Application
     */
    protected $application;
    /**
     * @var \Doctrine\DBAL\Schema\Schema
     */
    protected $schema;
    /**
     * @var \Cti\Storage\Schema
     */
    protected $inputSchema;

    public function convert(\Cti\Storage\Schema $inputSchema)
    {
        $this->inputSchema = $inputSchema;
        $this->schema = $this->application->getManager()->create('\Doctrine\DBAL\Schema\Schema');

        foreach($this->inputSchema->getModels() as $model) {

        }
        return $this->schema;
    }
}