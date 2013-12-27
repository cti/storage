<?php

namespace Di;

/**
 * Class Manager
 * @package Di
 */
class Manager
{
    /**
     * @var Configuration
     */
    protected $config;

    /**
     * @var array
     */
    protected $instance = array();

    /**
     * @var array
     */
    protected $callback = array();

    /**
     * @param Configuration $config
     */
    public function __construct(Configuration $config = null)
    {
        if (!$config) {
            $config = new Configuration;
        }
        $this->instance[__CLASS__] = $this;
        $this->instance['Di\Config'] = $this->config = $config;
    }

    /**
     * @param $class
     * @return mixed
     * @throws Exception
     */
    public function get($class)
    {
        if (!$class) {
            throw new Exception();
        }
        if (!isset($this->instance[$class])) {
            $this->instance[$class] = $this->create($class);
        }
        return $this->instance[$class];
    }

    /**
     * @param $class
     * @param array $config
     * @return object
     */
    public function create($class, $config = array())
    {
        $configuration = $this->config->get($class);

        if (!method_exists($class, '__construct')) {
            $instance = new $class;

        } else {
            // define complete params
            $parameters = array_merge(get_object_vars($configuration), $config);
            $parameters['config'] = $parameters;

            // launch with null instance, cause it's constructor
            $callback = $this->getCallback($class, '__construct');
            $instance = $callback->launch(null, $parameters, $this);
        }

        foreach ($configuration as $k => $v) {
            if (property_exists($class, $k)) {
                if (Reflection::getReflectionProperty($class, $k)->isPublic()) {
                    $instance->$k = $v;
                }
            }
        }

        return $instance;
    }

    /**
     * @param $instance
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function call($instance, $method, $arguments)
    {
        if(!is_object($instance)) {
            $class = $instance;
            $instance = $this->get($instance);
        } else {
            $class = get_class($instance);
        }
        $callback = $this->getCallback($class, $method);
        return $callback->launch($instance, $arguments, $this);
    }

    /**
     * @param $class
     * @param $method
     * @return Callback
     */
    protected function getCallback($class, $method)
    {
        if (!isset($this->callback[$class])) {
            $this->callback[$class] = new Callback($class, $method);
        }
        return $this->callback[$class];
    }
}