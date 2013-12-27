<?php

namespace Application;

use Exception;
use Util\String;

class Web extends Base
{
    /**
     * @var string
     */
    public $base = '/';

    /**
     * @var string
     */
    public $serverName;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $chain;

    /**
     * @throws Exception
     */
    function init()
    {
        if (!$this->serverName) {
            $this->serverName = $_SERVER['SERVER_NAME'];
        }
        list($this->url) = explode('?', $_SERVER['REQUEST_URI']);
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        // validate base
        if ($this->base) {
            if ($this->base[0] != '/') {
                throw new Exception('base property not begins with /');
            } elseif ($this->base[strlen($this->base) - 1] != '/') {
                throw new Exception('base property not ends with /');
            }
        }

        // define chain
        $request = substr($this->url, strlen($this->base));
        $this->chain = strlen($request) ? explode('/', $request) : array();
        if (count($this->chain)) {
            foreach ($this->chain as $k => $v) {
                if ($v === '') {
                    unset($this->chain[$k]);
                }
            }
            $this->chain = array_values($this->chain);
        }
    }

    function process($class)
    {
        if (!count($this->chain)) {
            $nick = 'index';
        } else {
            $nick = array_shift($this->chain);
        }

        $slug = String::convertToCamelCase($nick);

        try {
            foreach (explode(' ', 'get post match') as $http_method) {
                if ($http_method == $this->method || $http_method == 'match') {
                    $method_name = $http_method . $slug;
                    if (method_exists($class, $method_name)) {
                        if (method_exists($class, 'validateCall')) {
                            $this->call($class, 'validateCall', array($method_name));
                        }
                        return $this->call($class, $method_name, $this->chain);
                    }
                }
            }
            throw new Exception("Not found", 404);

        } catch (Exception $e) {

            if (method_exists($class, 'catchException')) {
                $this->call($class, 'catchException', array($e));

            } else {
                echo $e->getMessage();
            }
        }
    }
}