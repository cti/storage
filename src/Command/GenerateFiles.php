<?php

namespace Cti\Storage\Command;

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
     * @var Cti\Core\Application
     */
    protected $application;

    protected function configure()
    {
        $this
            ->setName('generate:files')
            ->setDescription('Generate php classes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $this->application->getSchema();

        $fs = new Filesystem();
        $fs->dumpFile(
            $this->application->getPath('build php Storage Master.php'), 
            $this->application->getManager()->create('Cti\Storage\Generator\Master', array(
                'schema' => $schema
            ))
        );

        foreach($schema->models as $model) {
            
            $fs->dumpFile(
                $this->application->getPath('build php Storage Model ' . $model->class_name . 'Base.php'), 
                $this->application->getManager()->create('Cti\Storage\Generator\Model', array(
                    'model' => $model
                ))
            );

            $fs->dumpFile(
                $this->application->getPath('build php Storage Repository ' . $model->class_name . 'Repository.php'), 
                $this->application->getManager()->create('Cti\Storage\Generator\Repository', array(
                    'model' => $model
                ))
            );

            if($model->hasOwnQuery()) {
                $fs->dumpFile(
                    $this->application->getPath('build php Storage Query ' . $model->class_name . 'Select.php'), 
                    $this->application->getManager()->create('Cti\Storage\Generator\Select', array(
                        'model' => $model
                    ))
                );
            }
        }
    }
}