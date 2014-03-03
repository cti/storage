<?php

namespace Storage\Behaviour;

class Link
{
    public $list = array();

    function getPk()
    {
        $pk = array();
        foreach($this->list as $model) {
            foreach($model->getPk() as $key) {
                $pk[] = $key;
            }
        }
        return $pk;
    }

    function getAdditionalProperties()
    {
        $properties = array();
        foreach($this->getPk() as $key) {
            $properties[] = $this->getAdditionalProperty($key);
        }
        return $properties;
    }

    function getAdditionalProperty($name)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }
        if(!in_array($name, $this->getPk())) {
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