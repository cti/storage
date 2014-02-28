<?php

namespace Storage\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDatabase extends Command
{
    /**
     * @inject
     * @var Application\Locator
     */
    protected $locator;

    protected function configure()
    {
        $this
            ->setName('generate:database')
            ->setDescription('Generate php classes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}