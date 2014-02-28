<?php

namespace Application;

/**
 * File locator
 * @package Application
 */
class Locator
{
    protected $locations;

    protected $project;
    protected $base;

    public function __construct($project)
    {
        $this->locations = array(
            $this->project = $project,
            $this->base = dirname(dirname(dirname(__DIR__)))
        );
    }

    public function path()
    {
        $args = func_get_args();
        if(count($args) == 1) {
            $args = explode(' ', $args[0]);
        }
        foreach ($this->locations as $location) {
            $file = $location . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $args);
            if (file_exists($file) || is_dir($file)) {
                return $file;
            }
        }
        return $this->project . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $args);
    }

    public function project($path = '')
    {
        $args = func_get_args();
        if(count($args) == 1) {
            $args = explode(' ', $args[0]);
        }
        return $this->project . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $args);
    }

    public function base($path = '')
    {
        $args = func_get_args();
        if(count($args) == 1) {
            $args = explode(' ', $args[0]);
        }
        return $this->base . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $args);
    }

    public function createDirectory($location)
    {
        if (!is_dir($location)) {
            $parent = dirname($location);
            if(!is_dir($parent)) {
                $this->createDirectory($parent);
            }
            mkdir($location);
        } 

    }
}