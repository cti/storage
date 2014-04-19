<?php

namespace Cti\Storage;

use Cti\Core\Application;
use Symfony\Component\Finder\Finder;

class Extension
{
    protected $path;

    function init(Application $application)
    {
        $this->path = dirname(__DIR__);

        $console = $application->getConsole();
        foreach($this->getClasses('Command') as $class) {
            $console->add($application->getManager()->get("Cti\Storage\\$class"));
        }

        $application->register('schema', 'Cti\Storage\Schema');
        $application->register('storage', 'Storage\Storage');
    }

    /**
     * get class list
     * @param string $namespace
     * @return array
     */
    public function getClasses($namespace)
    {
        $classes = array();
        $path = $this->getPath("src $namespace");

        if(is_dir($path)) {
            $finder = new Finder();
            foreach($finder->files()->name('*.php')->in($path) as $file) {
                $classes[] = $namespace . '\\' . $file->getBasename('.php');
            }
        }

        return $classes;
    }

    public function getPath($string)
    {
        $args = func_get_args();
        if(count($args) == 1) {
            $args = explode(' ', $args[0]);
        }

        $args = array_filter($args, 'strlen');
        array_unshift($args, $this->path);

        return implode(DIRECTORY_SEPARATOR, $args);

    }
}