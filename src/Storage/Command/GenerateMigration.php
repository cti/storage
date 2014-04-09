<?php

namespace Cti\Storage\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Cti\Util\String;

class GenerateMigration extends Command
{
    /**
     * @inject
     * @var Application\Locator
     */
    protected $locator;

    /**
     * @inject
     * @var Di\Manager
     */
    protected $manager;

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
        $name = $input->getArgument('name');

        $id = implode('_', $name);
        $class = String::convertToCamelCase($id);

        $timestamp = time();

        $filename = $this->locator->path('resources php migrations ' . date('Ymd_His', $timestamp) . '_' . $id . '.php');
        $migration = $this->manager->create('Storage\Generator\Migration', array(
            'class' => $class,
            'timestamp' => $timestamp
        ));

        $fs = new Filesystem();

        $fs->dumpFile($filename, $migration);
    }
}