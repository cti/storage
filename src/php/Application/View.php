<?php

namespace Application;

use BadMethodCallException;
use Exception;
use OutOfRangeException;

/**
 * Template engine
 * @package Application
 */
class View
{
    /**
     * @var \Application\Locator 
     */
    protected $locator;

    /** 
     * @var mixed 
     */
    protected $context;

    /**
     * @param \Application\Locator $locator 
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
    public function render($name, $data = array(), $context = null)
    {
        try {
            $this->context = $context;
            extract($data);
            ob_start();         
            include $this->locator->path('resources php view '.func_get_arg(0).'.php');
            return ob_get_clean();
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * get context property
     * @param string $name
     */
    public function __get ($name)
    {
        if(is_object($this->context)) {
            return $this->context->$name;
        }
        throw new OutOfRangeException("Property $name not found");
    }

    /** 
     * call context method
     * @param string $name
     * @param array  $arguments
     */
    public function __call ($name, $arguments)
    {
        if(is_object($this->context)) {
            return call_user_func_array(array($this->context, $name), $arguments);
        }
        throw new BadMethodCallException("Method $name not found");
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
