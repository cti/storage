<?php

namespace Cti\Storage\Command;

use Cti\Storage\Schema;
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
            ->setName('generate:files')
            ->setDescription('Generate php classes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var $schema Schema
         */
        $schema = $this->application->getStorage()->getSchema();

        $fs = new Filesystem();

        $fs->dumpFile(
            $this->application->getProject()->getPath('build php Storage Master.php'),
            $this->fenom->render("php master", array(
                'schema' => $schema
            ))
        );

        foreach($schema->getModels() as $repositoryGenerator) {

            $path = $this->application->getProject()->getPath('build php Storage Model ' . $repositoryGenerator->getClassName() . 'Base.php');
            $modelGenerator = $this->application->getManager()->create('Cti\Storage\Generator\Model', array(
                'model' => $repositoryGenerator
            ));
            $modelSource = $modelGenerator->getCode();

            $fs->dumpFile(
                $path,
                $modelSource
            );

            $path = $this->application->getProject()->getPath('build php Storage Repository ' . $repositoryGenerator->getClassName() . 'Repository.php');
            $repositoryGenerator = $this->application->getManager()->create('Cti\Storage\Generator\Repository', array(
                'model' => $repositoryGenerator
            ));

            $repositorySource = $repositoryGenerator->getCode();
            $fs->dumpFile(
                $path,
                $repositorySource
            );

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