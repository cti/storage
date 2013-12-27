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

    function get($class)
    {
        return $this->manager->get($class);
    }

    function call($class, $method, $arguments = array())
    {
        return $this->manager->call($class, $method, $arguments);
    }
}