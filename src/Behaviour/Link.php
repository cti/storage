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
        $model->removeSequence();

        foreach ($this->list as $foreign) {
            if ($foreign->hasBehaviour('log') && !$model->hasBehaviour('log')) {
                $model->addBehaviour('log');
            }
        }
    }


    /**
     * @param Model $model
     * @return Model
     */
    function getForeignModel(Model $model)
    {
        $models = array_values($this->list);
        if ($models[0] == $model) {
            return $models[1];
        }
        return $models[0];
    }

    function getPk()
    {
        $result = array();
        foreach ($this->list as $model) {
            foreach ($model->getPk() as $field) {

                // log behaviour must be copied in init
                // so link model has own log property
                if ($model->getProperty($field)->getBehaviour() instanceof Log) {
                    continue;
                }

                if (in_array($field, $result)) {
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
        foreach ($this->getPk() as $field) {
            $properties[] = $this->getProperty($field);
        }
        return $properties;
    }

    function getProperty($name)
    {
        if (in_array($name, $this->getPk())) {
            foreach ($this->list as $model) {
                if ($model->hasProperty($name)) {
                    return $model->getProperty($name);
                }
            }
        }
    }


}