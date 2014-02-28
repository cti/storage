<?php

namespace Storage;

use Storage\Component\Model;
use Storage\Component\Link;
use Symfony\Component\Finder\Finder;
use Util\String;

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

    function init($dump = null)
    {
        if($dump) {
            $this->loadDump($dump);

        } else {
            $this->processMigrations();
            
        }
    }

    public function createModel($name, $comment, $properties = array())
    {
        return $this->models[$name] = new Model($name, $comment, $properties);
    }

    public function getModel($name)
    {
        return $this->models[$name];
    }

    public function getModels()
    {
        return array_values($this->models);
    }

    public function createLink(Model $a, Model $b)
    {
        $link = new Link($a, $b);
        return isset($this->models[$link->name]) ? $this->models[$link->name] : $this->models[$link->name] = $link;
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

    function getDump()
    {
        return json_encode($this);
    }

    function loadDump($dump)
    {
    }
}