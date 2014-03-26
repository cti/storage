<?php

namespace Storage\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateFiles extends Command
{
    /**
     * @inject
     * @var Di\Manager
     */
    protected $manager;

    protected function configure()
    {
        $this
            ->setName('generate:files')
            ->setDescription('Generate php classes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locator = $this->manager->get('Application\Locator');
        $schema = $this->manager->get('Storage\Schema');

        $fs = new Filesystem();
        $fs->dumpFile(
            $locator->path('out php Storage Storage.php'), 
            $this->manager->create('Storage\Generator\Storage', array(
                'schema' => $schema
            ))
        );

        foreach($schema->models as $model) {
            
            $fs->dumpFile(
                $locator->path('out php Storage Model ' . $model->class_name . 'Base.php'), 
                $this->manager->create('Storage\Generator\Model', array(
                    'model' => $model
                ))
            );

            $fs->dumpFile(
                $locator->path('out php Storage Repository ' . $model->class_name . 'Repository.php'), 
                $this->manager->create('Storage\Generator\Repository', array(
                    'model' => $model
                ))
            );

            if($model->hasOwnQuery()) {
                $fs->dumpFile(
                    $locator->path('out php Storage Query ' . $model->class_name . 'Select.php'), 
                    $this->manager->create('Storage\Generator\Select', array(
                        'model' => $model
                    ))
                );
            }
        }
    }
}