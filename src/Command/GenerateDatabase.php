<?php

namespace Cti\Storage\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cti\Storage\Adapter;
use Cti\Storage\Generator;

class GenerateDatabase extends Command
{
    /**
     * @inject
     * @var \Cti\Core\Application
     */
    protected $application;
    /**
     * @inject
     * @var \Cti\Storage\Adapter\DBAL
     */
    protected $dbal;

    /**
     * @inject
     * @var \Cti\Storage\Generator\Database
     */
    protected $generator;
    /**
     * @inject
     * @var \Cti\Storage\Converter\DBAL
     */
    protected $dbalConverter;
    protected $testMode;

    protected function configure()
    {
        $this
            ->setName('generate:database')
            ->setDescription("Generate all storage stuff in database")
            ->addOption('test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var $schema \Cti\Storage\Schema
         */
        $schema = $this->application->getSchema();
        $dbalToSchema = $this->dbalConverter->convert($schema);
        $dbalFromSchema = $this->dbal->getSchemaManager()->createSchema();
        $this->generator->setToSchema($dbalToSchema);
        $this->generator->setFromSchema($dbalFromSchema);
        $queries = $this->generator->migrate();
        echo implode(";\n",$queries) . ';';
        if ($input->getOption('test') != true) {
            foreach($queries as $query) {
                $this->dbal->executeQuery($query);
            }
        }
    }


}