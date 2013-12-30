<?php

namespace Application;

class Console extends Base
{
    private $arguments;

    function init()
    {
        global $argv;
        array_shift($argv);
        $this->arguments = $argv;
    }

    function process($class)
    {
        $command = array_shift($this->arguments);
        if (!$command) {
            $command = 'index';
        }

        $method = 'action' . $this->convertSlug($command);

        if (method_exists($class, $method)) {
            return $this->call($class, $method, $this->arguments);
        }

        throw new \Exception("Action $command not found");
    }
}