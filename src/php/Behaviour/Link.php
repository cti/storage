<?php

namespace Cti\Storage\Behaviour;

use Cti\Storage\Component\Model;
use Cti\Storage\Component\Reference;

class Link extends Behaviour
{

    /**
     * @var Model[]
     */
    protected $list = array();

    /**
     * @var Reference[]
     */
    protected $references = array();

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

    public function registerReference($modelName, $reference)
    {
        $this->references[$modelName] = $reference;
    }

    public function getReferences()
    {
        return $this->references;
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

                // Take a reference to that model. We will receive real field's name.
                $reference = $this->references[$model->getName()];
                $name = $reference->getDestinationAlias() != $reference->getDestination() ? $field . '_' . $reference->getDestinationAlias(): $field;

                $result[] = $name;
            }
        }
        return $result;
    }

    function getProperties()
    {
        return array();
    }

    function getProperty($name)
    {
        if (in_array($name, $this->getPk())) {
            foreach($this->references as $reference) {
                $properties = $reference->getProperties();
                if (isset($properties[$name])) {
                    return $properties[$name];
                }
            }
        }
    }

    public function makeReferencedFieldsRequired()
    {
        foreach($this->references as $reference) {
            foreach($reference->getProperties() as $property) {
                $property->setRequired(true);
            }
        }
    }


}