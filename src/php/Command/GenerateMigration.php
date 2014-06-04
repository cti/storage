<?php

namespace Cti\Storage\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Cti\Core\String;

class GenerateMigration extends Command
{
    /**
     * @inject
     * @var \Build\Application
     */
    protected $application;

    protected function configure()
    {
        $this
            ->setName('generate:migration')
            ->setDescription('Generate new storage migration')
            ->addArgument('name', InputArgument::IS_ARRAY, 'Migration name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = implode('_', $input->getArgument('name'));
        $class = String::convertToCamelCase($id);

        $timestamp = time();

        $filename = $this->application->getProject()->getPath('resources php migrations ' . date('Ymd_His', $timestamp) . '_' . $id . '.php');
        $migration = $this->application->getManager()->create('Cti\Storage\Generator\Migration', array(
            'class' => $class,
            'timestamp' => $timestamp
        ));

        $fs = new Filesystem();
        $fs->dumpFile($filename, $migration);
        if (!defined('TEST_ENVIRONMENT')) {
            echo 'Migration created. ' . $filename . PHP_EOL;
        }
    }
}