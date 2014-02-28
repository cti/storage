<?php

namespace Storage\Component;

use Util\String;

class Property
{
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
}