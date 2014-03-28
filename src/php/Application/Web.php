<?php

namespace Application;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Util\String;

/**
 * Web application implementation
 * @package Application
 */
class Web
{

    /**
     * @var string base url
     */
    public $base = '/';

    /**
     * @var string
     */
    protected $method;

    /**
     * @inject
     * @var Di\Manager
     */
    protected $manager;

    public function init()
    {
        // validate base
        if ($this->base != '/') {
            if ($this->base[0] != '/') {
                throw new Exception('base property not begins with /');
            } elseif ($this->base[strlen($this->base) - 1] != '/') {
                throw new Exception('base property not ends with /');
            }
        }

        $request = Request::createFromGlobals();
        $this->manager->register($request);
        $this->manager->register(new Session());

        $location = substr($request->getPathInfo(), strlen($this->base));
        
        $this->method = strtolower($request->getMethod());
        $this->chain = strlen($location) ? explode('/', $location) : array();

        if(count($this->chain)) {
            foreach ($this->chain as $k => $v) {
                if ($v === '') {
                    unset($this->chain[$k]);
                }
            }
            $this->chain = array_values($this->chain);
        }
    }

    /**
     * @param  string $class class to process
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function process($class)
    {
        try {

            $slug = count($this->chain) ? array_shift($this->chain) : 'index';
            $method = $this->method . String::convertToCamelCase($slug);

            if(method_exists($class, $method)) {
                $result = $this->manager->call($class, $method, array_merge($this->chain, array(
                    'chain' => $this->chain
                )));
            } elseif(method_exists($class, 'processChain')) {
                if($slug != 'index') {
                    array_unshift($this->chain, $slug);
                }
                $result = $this->manager->call($class, 'processChain', array(
                    'chain' => $this->chain
                ));
            } else {
                throw new Exception("Not found", 404);
            }


        } catch(Exception $e) {
            if(!method_exists($class, 'processException')) {
                throw $e;
            }
            $result = $this->manager->call($class, 'processException', array($e, 'exception' => $e));
        }

        echo $result;
    }

    /**
     * generate relative aplication url
     * @param  string $location 
     * @return string
     */
    function getUrl($location = '')
    {
        return $this->base . implode('/', func_get_args());
    }    
}