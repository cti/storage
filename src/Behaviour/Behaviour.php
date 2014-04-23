<?php

namespace Cti\Storage\Behaviour;

use Cti\Core\String;
use Cti\Di\Reflection;
use Cti\Storage\Component\Model;
use Cti\Storage\Component\Property;
use Exception;

abstract class Behaviour
{
    /**
     * @param string $nick
     * @param array $config
     * @return Behaviour
     * @throws \Exception
     */
    public static function create(Model $model, $nick, $config = array())
    {
        $class_name = sprintf('Cti\Storage\Behaviour\%s', String::convertToCamelCase($nick));
        if (!Reflection::getReflectionClass($class_name)->isSubclassOf(__CLASS__)) {
            throw new Exception(sprintf('Behaviour %s not found!', $nick));
        }
        return new $class_name($model, $config);
    }

    /**
     * @var Property[]
     */
    protected $properties = array();

    /**
     * @param array $config
     */
    protected function __construct(Model $model, $config)
    {
        foreach ($config as $k => $v) {
            if (!property_exists($this, $k)) {
                throw new Exception(sprintf("Property %s.%s not found!", get_called_class(), $k));
            }
            $this->$k = $v;
        }

        $this->init($model);
    }

    /**
     * properties initialization
     */
    function init(Model $model)
    {
    }

    public function getPk()
    {
        return array();
    }

    function getProperty($name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }
    }

    function getProperties()
    {
        return array_values($this->properties);
    }

}