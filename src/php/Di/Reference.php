<?php

namespace Di;

/**
 * Class Reference
 * @package Di
 */
class Reference {

    /**
     * @var string
     */
    protected $class;

    /**
     * @param $class
     */
    function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * @param Manager $manager
     * @return object
     */
    function getInstance(Manager $manager)
    {
        return $manager->get($this->class);
    }
}