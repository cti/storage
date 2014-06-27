<?php

namespace Cti\Storage\Command;

use Cti\Storage\Schema;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateStorage extends Command
{
    /**
     * @inject
     * @var \Build\Application
     */
    protected $application;

    /**
     * @inject
     * @var \Cti\Core\Module\Fenom
     */
    protected $fenom;

    protected function configure()
    {
        $this
            ->setName('generate:storage')
            ->addArgument('debug')            
            ->setDescription('Generate php classes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var $schema Schema
         */
        $schema = $this->application->getStorage()->getSchema();

        $debug = $input->getArgument('debug') == true;

        $fs = new Filesystem();

        $fs->dumpFile(
            $this->application->getProject()->getPath('build php Storage Master.php'),
            $this->fenom->render("master", array(
                'schema' => $schema
            ))
        );
        if($debug) {
            echo '- master' . PHP_EOL;
        }


        foreach($schema->getModels() as $model) {

            $modelGenerator = $this->application->getManager()->create('Cti\Storage\Generator\Model', array(
                'model' => $model
            ));
            $modelSource = $modelGenerator->getCode();
            $path = $this->application->getProject()->getPath('build php Storage Model ' . $model->getClassName() . 'Base.php');
            $fs->dumpFile($path, $modelSource);

            $repositoryGenerator = $this->application->getManager()->create('Cti\Storage\Generator\Repository', array(
                'model' => $model
            ));

            $repositorySource = $repositoryGenerator->getCode();
            $path = $this->application->getProject()->getPath('build php Storage Repository ' . $model->getClassName() . 'Repository.php');
            $fs->dumpFile($path, $repositorySource);

            if($debug) {
                echo '- generate ' . $model->getClassName() . PHP_EOL;
            }


//            if($model->hasOwnQuery()) {
//                $fs->dumpFile(
//                    $this->application->getPath('build php Storage Query ' . $model->class_name . 'Select.php'),
//                    $this->application->getManager()->create('Cti\Storage\Generator\Select', array(
//                        'model' => $model
//                    ))
//                );
//            }
        }
    }
}