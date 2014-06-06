<?php

namespace Cti\Storage\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Cti\Storage\Adapter;
use Cti\Storage\Generator;

class GenerateDatabase extends Command
{
    /**
     * @inject
     * @var \Build\Application
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
        $fs = new Filesystem();
        $schema = $this->application->getStorage()->getSchema();

        // create.sql
        $dbalToSchema = $this->dbalConverter->convert($schema);
        $dbalFromSchema = $schema = new \Doctrine\DBAL\Schema\Schema();
        $this->generator->setToSchema($dbalToSchema);
        $this->generator->setFromSchema($dbalFromSchema);
        $queries = $this->generator->migrate();

        $create = $this->application->getProject()->getPath('build sql create.sql');

        $output->writeln('Create ' . $create);
        $fs->dumpFile($create, implode(";\n",$queries) . ';');

        // migrate.sql
        $dbalFromSchema = $this->dbal->getSchemaManager()->createSchema();
        $this->generator->setFromSchema($dbalFromSchema);
        $queries = $this->generator->migrate();

        $migrate = $this->application->getProject()->getPath('build sql migrate.sql');

        $output->writeln('Create ' . $migrate);
        $fs->dumpFile($migrate, implode(";\n",$queries) . (count($queries) ? ';' : ''));

        echo implode(";\n",$queries) . ';';
        if ($input->getOption('test') != true) {
            foreach($queries as $query) {
                $this->dbal->executeQuery($query);
            }
        }

    }
}