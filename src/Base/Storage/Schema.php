<?php

namespace Base\Storage;

use Base\Storage\Component\Model;
use Base\Storage\Component\Link;
use Base\Util\String;
use Exception;
use Symfony\Component\Finder\Finder;

class Schema
{
    /**
     * @inject
     * @var Di\Manager 
     */
    protected $manager;

    /**
     * @inject
     * @var Application\Locator
     */
    protected $locator;

    public $models = array();

    function init($dump = null)
    {
        if($dump) {
            $this->loadDump($dump);
        } else {
            $this->processMigrations();
            $this->processRelation();
        }
    }

    public function createModel($name, $comment, $properties = array())
    {
        return $this->models[$name] = $this->manager->create('Storage\Component\Model', array(
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

        $link = $this->manager->create('Storage\Component\Model', array('name' => $name, 'comment' => $name));
        
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

    public function restore()
    {
        $dump = \Generated\Storage::getDump();
        foreach ($dump['models'] as $key => $data) {
            $this->models[$key] = Model::restore($this, $data);
        }

        foreach ($dump['links'] as $key => $data) {
            $this->links[$key] = Link::restore($this, $data);
        }
    }

    function processMigrations()
    {
        $finder = new Finder();

        $finder
            ->files()
            ->name("*.php")
            ->in($this->locator->path('resources php migrations'));

        foreach($finder as $file) {

            $date = substr($file->getFileName(), 0, 8);
            $time = substr($file->getFileName(), 9, 6);
            $index = substr($file->getBasename('.php'), 16);
            $name = String::convertToCamelCase($index);

            $class = 'Migration\\' . $name . '_' . $date . '_' . $time;

            if(!class_exists($class, false)) {
                include $file->getPathname();
            }

            $this->manager->get($class)->process($this);
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

    function getDump()
    {
        return 'dump for schema';
    }

    function loadDump($dump)
    {
    }
}