<?php

namespace Application;

/**
 * Class Console
 * @package Application
 */
class Console extends Base
{
    /**
     * command line arguments
     * @var array
     */
    private $arguments;

    /**
     * initialization
     */
    function init()
    {
        global $argv;
        array_shift($argv);
        $this->arguments = $argv;
    }

    /**
     * @param $class
     * @return mixed
     * @throws \Exception
     */
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