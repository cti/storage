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
     * @var Cti\Core\Application
     */
    protected $application;

    public $models = array();

    function init($dump = null)
    {
        $this->processMigrations();
        $this->processRelation();
    }

    public function createModel($name, $comment, $properties = array())
    {
        return $this->models[$name] = $this->application->getManager()->create('Cti\Storage\Component\Model', array(
                'name' => $name, 
                'comment' => $comment, 
                'properties' => $properties
            )
        );
    }

    public function getModel($name)
    {
        if(!isset($this->models[$name])) {
            throw new Exception(sprintf("Model %s was not yet defined", $name));
        }
        return $this->models[$name];
    }

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
                $k = $v->name;
            }
            if($k == $v->name) {
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
        
        $link->createBehaviour('link', array(
            'list' => $list
        ));

        foreach($mapping as $alias => $model) {
            if($model->hasBehaviour('log') && !$link->hasBehaviour('log')) {
                $link->createBehaviour('log');
            }

            $link->hasOne($model)->usingAlias($alias)->referencedBy($name);
            $model->registerLink($link, $relation[$alias]);
        }

        return $this->models[$name] = $link;
    }

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

            if(!class_exists($class_name)) {
                include $file->getRealPath();
            }
            $this->application->getManager()->get($class)->process($this);
        }
    }

    function processRelation()
    {
        foreach($this->models as $model) {
            foreach($model->relations as $relation) {
                $relation->process($this);
            }
        }
    }
}