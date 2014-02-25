<?php

namespace Web;

/**
 * Template engine
 * @package Web
 */
class View
{
    /**
     * @var \Web\Locator 
     */
    protected $locator;

    /**
     * @param \Web\Locator $locator 
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
        extract($data);
        ob_start();         
        include $this->locator->path('resources php view '.func_get_arg(0).'.php');
        return ob_get_clean();
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
