<?php

namespace Storage\Component;

use Util\String;

class Model
{
    public $name;
    public $class_name;
    public $repository_class;
    public $comment;

    public $properties = array();

    function __construct($name, $comment, $properties)
    {
        $this->name = $name;
        $this->name_many = String::pluralize($name);

        $this->class_name = String::convertToCamelCase($name);
        $this->class_name_many = String::pluralize($this->class_name);

        $this->repository_class = 'Storage\Repository\\' . $this->class_name.'Repository';
        $this->model_class = 'Storage\Model\\' . $this->class_name.'Base';

        $this->comment = $comment;

        foreach ($properties as $key => $config) {
            if (is_string($config)) {
                $config = array('name' => $key, 'comment' => $config);
            } elseif (is_array($config) && !is_numeric($key)) {
                if(isset($config[0])) {
                    array_unshift($config, $key);
                } else {
                    $config['name'] = $key;
                }
            }
            $property = new Property($config);
            $this->properties[$property->name] = $property;
        }        
    }

    function getName()
    {
        return $this->name;
    }

    function getClassName()
    {
        return $this->class_name;
    }

    function getRepositoryClass()
    {
        return $this->repository_class;
    }

    function getPk()
    {
        return array('id_person');
    }

    function getProperty($name)
    {
        return $this->properties[$name];
    }

    function getProperties()
    {
        return array_values($this->properties);
    }
}