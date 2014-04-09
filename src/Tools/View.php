<?php

namespace Cti\Tools;

use BadMethodCallException;
use Exception;
use OutOfRangeException;

/**
 * Template engine
 * @package Cti\Tools
 */
class View
{
    /**
     * @var Cti\Tools\Locator 
     */
    protected $locator;

    /**
     * @param Cti\Tools\Locator $locator 
     */
    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * render template
     * @param string $name
     * @param array $data 
     * @return string 
     */
    public function render($name, $data = array())
    {
        try {
            extract($data);
            ob_start();
            include $this->locator->path('resources php view '.func_get_arg(0).'.php');
            return ob_get_clean();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * show rendered template
     * @param string $name 
     * @param array $data 
     */
    public function show($name, $data = array()) 
    {
        echo $this->render($name, $data);
    }
}
