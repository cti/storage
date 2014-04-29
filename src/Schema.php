<?php

namespace Cti\Storage;

use Cti\Storage\Component\Model;
use Cti\Storage\Component\Link;
use Cti\Core\String;

use Exception;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Schema
{
    /**
     * @inject
     * @var \Cti\Di\Manager
     */
    protected $manager;

    /**
     * @inject
     * @var \Cti\Core\Application
     */
    protected $application;

    /**
     * model list
     * @var Model[]
     */
    protected $models = array();

    function init()
    {
        $this->processMigrations();
        $this->completeRelations();
    }

    /**
     * Create new model
     * @param $name
     * @param $comment
     * @param array $properties
     * @return Model
     */
    public function createModel($name, $comment, $properties = array())
    {
        return $this->models[$name] = $this->manager->create('Cti\Storage\Component\Model', array(
                'name' => $name,
                'comment' => $comment, 
                'properties' => $properties
            )
        );
    }

    public function removeModel($name)
    {
        if (empty($this->models[$name])) {
            throw new \Exception("Model $name not found in schema");
        }
        unset($this->models[$name]);
    }


    /**
     * @return Model[]
     */
    public function getModels()
    {
        return $this->models;
    }


    /**
     * @param string $name
     * @return Component\Model
     * @throws \Exception
     */
    public function getModel($name)
    {
        if(!isset($this->models[$name])) {
            throw new Exception(sprintf("Model %s was not yet defined", $name));
        }
        return $this->models[$name];
    }

    /**
     * @param Model[] $list
     * @return Model
     * @throws \Exception
     */
    public function createLink($list)
    {
        if($list instanceof Model) {
            $list = func_get_args();
        }

        $mapping = array();
        $name = array();
        if(count($list) != 2) {
            throw new \Exception("Link must contain 2 models");
        }

        $start = $end = array();

        foreach($list as $k => $v) {
            if(is_numeric($k)) {
                $k = $v->getName();
            }
            if($k == $v->getName()) {
                $start[] = $k;
            } else {
                $end[] = $k;
            }
            $mapping[$k] = $v;
        }

        $relation = array_combine(array_keys($mapping), array_reverse(array_keys($mapping)));

        sort($start);
        sort($end);

        $name = $start;
        foreach($end as $v) {
            $name[] = $v;
        }

        $name[] = 'link';
        $name = implode('_', $name);

        $link = $this->application->getManager()->create(
            'Cti\Storage\Component\Model',
            array(
                'name' => $name,
                'comment' => $name
            )
        );

        $link->addBehaviour('link', array(
            'list' => $list
        ));

        foreach($mapping as $alias => $model) {
            $link->hasOne($model)->usingAlias($alias)->referencedBy($name);
            $model->registerLink($link, $relation[$alias]);
        }

        return $this->models[$name] = $link;
    }

    /**
     * process migrations from filesystem
     */
    function processMigrations()
    {
        $filesystem = new Filesystem;
        $migrations = $this->application->getPath('build php Storage Migration');
        if($filesystem->exists($migrations)) {
            $filesystem->remove($migrations);
        }
        $filesystem->mkdir($migrations);

        $finder = new Finder();

        $finder
            ->files()
            ->name("*.php")
            ->in($this->application->getPath('resources php migrations'));

        foreach($finder as $file) {

            $date = substr($file->getFileName(), 0, 8);
            $time = substr($file->getFileName(), 9, 6);
            $index = substr($file->getBasename('.php'), 16);
            $name = String::convertToCamelCase($index);

            $class_name = $name . '_' . $date . '_' . $time;
            $class = 'Storage\\Migration\\' . $class_name;
            
            $filesystem->copy($file->getRealPath(), $migrations . DIRECTORY_SEPARATOR . $class_name . '.php');

            if(!class_exists($class)) {
                include $file->getRealPath();
            }
            $this->application->getManager()->get($class)->process($this);
        }
    }

    /**
     * complete relation
     */
    function completeRelations()
    {
        foreach($this->models as $model) {
            foreach($model->getReferences() as $relation) {
                $relation->process($this);
            }
        }
    }

    /**
     * @return \Cti\Storage\Component\Sequence[]
     */
    public function getSequences()
    {
        $sequences = array();
        foreach($this->getModels() as $model) {
            $sequence = $model->getSequence();
            $sequences[$sequence->getName()] = $sequence;
        }
        return $sequences;
    }
}