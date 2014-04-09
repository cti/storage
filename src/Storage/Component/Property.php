<?php

namespace Cti\Storage\Component;

use Cti\Util\String;

class Property
{

    public $name;
    public $type;
    public $comment;
    public $foreignName;

    public $primary;
    public $required;
    public $behaviour;
    public $relation;

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
        $this->comment = isset($params['comment']) ? $params['comment'] : null;
        $this->foreignName = isset($params['foreignName']) ? $params['foreignName'] : null;
        $this->required = isset($params['required']) ? $params['required'] : false;
        $this->primary = isset($params['primary']) ? $params['primary'] : false;
        $this->behaviour = isset($params['behaviour']) ? $params['behaviour'] : false;
        $this->relation = isset($params['relation']) ? $params['relation'] : null;
        $this->readonly = isset($params['readonly']) ? $params['readonly'] : $this->primary;

        if(isset($params['model'])) {
            $this->model = $params['model'];
        }

        if (isset($params['type'])) {
            $this->type = $params['type'];
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
}