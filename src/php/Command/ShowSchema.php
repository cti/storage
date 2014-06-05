<?php
namespace Cti\Storage\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShowSchema extends Command
{
    /**
     * @inject
     * @var \Build\Application
     */
    protected $application;

    protected function configure()
    {
        $this
            ->setName('show:schema')
            ->setDescription('Show schema as json')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $this->application->getStorage()->getSchema();
        echo json_encode($schema->asArray());
    }
} 