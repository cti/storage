<?php

namespace Application;

class View
{
    protected $locator;

    public function __construct(Locator $locator)
    {
        $this->locator = $locator;
    }

    public function render($name, $data = array())
    {
        extract($data);
        ob_start();         
        include $this->locator->path('resources php view '.func_get_arg(0).'.php');
        return ob_get_clean();
    }

    public function show($name, $data = array()) 
    {
        echo $this->render($name, $data);
    }
}
