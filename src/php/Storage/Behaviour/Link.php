<?php

namespace Storage\Behaviour;

class Link
{
    public $model;
    public $list = array();

    function hasVirtualPk()
    {
        return false;
    }

    function getAdditionalPk()
    {
        $pk = array();
        foreach($this->list as $model) {
            foreach($model->getPk() as $key) {
                if(!$model->getProperty($key)->behaviour) {
                    if(!in_array($key, $pk)) {
                        $pk[] = $key;
                    }
                }
            }
        }
        return $pk;
    }

    function getAdditionalProperties()
    {
        $properties = array();
        foreach($this->getAdditionalPk() as $key) {
            $properties[] = $this->getAdditionalProperty($key);
        }
        return $properties;
    }

    function getAdditionalProperty($name)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        if(!in_array($name, $this->getAdditionalPk())) {
            return null;
        }
        foreach ($this->list as $model) {
            foreach($model->getProperties() as $property) {
                if($property->name == $name) {
                    return $this->properties[$name] = $property->copy($this);
                }
            }
        }
    }
}