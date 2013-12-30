<?php

namespace Application;

use Di\Manager;

abstract class Base
{
    /**
     * @var \Di\Manager
     */
    protected $manager;

    /**
     * @param Manager $manager
     */
    function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get class instance shortcut
     * @param $class
     * @return mixed
     */
    function get($class)
    {
        return $this->manager->get($class);
    }

    /**
     * Call method shortcut
     * @param $class
     * @param $method
     * @param array $arguments
     * @return mixed
     */
    function call($class, $method, $arguments = array())
    {
        return $this->manager->call($class, $method, $arguments);
    }

    /**
     * Convert under_score_string to CamelCaseString
     * @param $string
     * @return string
     */
    function convertSlug($string)
    {
        return implode('', array_map('ucfirst', explode('_', $string)));
    }

}