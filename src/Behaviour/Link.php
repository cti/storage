<?php

namespace Cti\Storage\Behaviour;

use Cti\Storage\Component\Model;

class Link extends Behaviour
{

    /**
     * @var Model[]
     */
    protected $list = array();

    function init(Model $model)
    {
        $model->removeBehaviour('id');
    }


    /**
     * @param Model $model
     * @return Model
     */
    function getForeignModel(Model $model)
    {
        foreach($this->list as $variant) {
            if($variant != $model) {
                return $variant;
            }
        }
    }

    function getPk()
    {
        $result = array();
        foreach($this->list as $model) {
            foreach($model->getPk() as $field) {

                if($model->getProperty($field)->getBehaviour() instanceof Log) {
                    continue;
                }

                if(in_array($field, $result)) {
                    continue;
                }

                $result[] = $field;
            }
        }
        return $result;
    }

    function getProperties()
    {
        $properties = array();
        foreach($this->getPk() as $field) {
            $properties[] = $this->getProperty($field);
        }
        return $properties;
    }

    function getProperty($name)
    {
        foreach($this->list as $model) {
            if($model->hasProperty($name)) {
                return $model->getProperty($name);
            }
        }
    }


}