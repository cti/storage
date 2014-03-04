<?php

namespace Storage\Component;

use Util\String;

class Property
{

    public $name;
    public $type;
    public $comment;

    public $primary;
    public $required;
    public $behaviour;
    public $mapping;

    public $setter;
    public $getter;

    public $min;
    public $max;
    
    public function __construct($params)
    {
        if(isset($params[0])) {
            // numeric keys
            switch(count($params)) {
                case 3:
                    $params = array('name' => $params[0], 'type' => $params[1], 'comment' => $params[2]);
                    break;
                default: 
                    throw new Exception("Error Processing property ");
            }
        }
        $this->name = $params['name'];
        $this->comment = $params['comment'];
        $this->required = isset($params['required']) ? $params['required'] : false;
        $this->primary = isset($params['primary']) ? $params['primary'] : false;
        $this->behaviour = isset($params['behaviour']) ? $params['behaviour'] : false;
        $this->readonly = isset($params['readonly']) ? $params['readonly'] : $this->primary;
        $this->mapping = isset($params['mapping']) ? $params['mapping'] : false;

        if(isset($params['model'])) {
            $this->model = $params['model'];
        }

        if (isset($params['type'])) {
            $this->type = $params['type'];
            if($this->type == 'virtual') {
                $this->virtual_name = String::convertToCamelCase($this->name);
            }
        } else {
            if (substr($this->name, 0, 3) == 'dt_') {
                $this->type = 'date';
            } elseif (substr($this->name, 0, 3) == 'id_') {
                $this->type = 'integer';
            } elseif (substr($this->name, 0, 3) == 'is_') {
                $this->type = 'boolean';
            } else {
                $this->type = 'string';
            }
        }

        if (isset($params['setter'])) {
            $this->setter = $params['setter'];
        } else {
            $this->setter = 'set'.String::convertToCamelCase($this->name);
        }

        if (isset($params['getter'])) {
            $this->getter = $params['getter'];
        } else {
            $this->getter = 'get'.String::convertToCamelCase($this->name);
        }

        if (isset($params['min'])) {
            $this->min = $params['min'];
        }

        if (isset($params['max'])) {
            $this->max = $params['max'];
        }
    }

    function copy($override = array())
    {
        $config = get_object_vars($this);
        foreach($override as $k => $v) {
            $config[$k] = $v;
        }
        return new Property($config);
    }

    function getForeignModelColumns()
    {
        if($this->type != 'virtual') {
            throw new Exception("Error processing foreign model");
        }
        $properties = array();
        foreach($this->model->getPk() as $name) {
            $property = $this->model->getProperty($name);
            if(!$property->behaviour) {
                $name = $property->name == $this->model->name ? $property->name : 'id_' . $this->name;
                $properties[] = new Property(array(
                    'name' => $name,
                    'mapping' => $property->name,
                    'comment' => $property->comment,
                    'type' => $property->type,
                ));
            }
        }
        return $properties;
    }
}