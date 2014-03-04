<?php

namespace Storage\Behaviour;

use Storage\Component\Property;

class Log
{
    public $model;

    protected $properties = array();

    public function init()
    {
        $this->properties = array(
            'v_start' => new Property(array(
                'behaviour' => true,
                'name' => 'v_start',
                'comment' => 'v_start',
                'type' => 'timestamp',
                'primary' => true,
            )),
            'v_end' => new Property(array(
                'behaviour' => true,
                'name' => 'v_end',
                'comment' => 'v_end',
                'type' => 'timestamp',
                'readonly' => true
            ))
        );
    }

    public function getAdditionalPk()
    {
        return array('v_start');
    }

    function getAdditionalProperties()
    {
        return array(
            $this->properties['v_start'],
            $this->properties['v_end'],
        );
    }

    function getAdditionalProperty($name)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
    }
}