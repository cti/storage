<?php

namespace Cti\Storage\Generator;

use Cti\Core\String;

class Reference
{
    /**
     * @inject
     * @var \Cti\Storage\Schema
     */
    public $schema;

    /**
     * @var \Cti\Storage\Component\Relation
     */
    public $reference;

    /**
     * @var \Cti\Storage\Component\Property
     */
    public $property;

    public function renderProperty()
    {

        $reference = $this->reference;
        $property_name = String::pluralize($reference->getReferencedBy());

        $source = $this->schema->getModel($reference->getSource());
        $destination = $this->schema->getModel($reference->getDestination());


        $source_model_class = $source->getModelClass();
        $result = <<<PROPERTY
    /**
     * @var {$source_model_class}[]
     */
    protected $$property_name;


PROPERTY;

        if($this->schema->getModel($reference->getSource())->hasBehaviour('link')) {
            $link = $this->schema->getModel($reference->getSource());
            $foreign = $link->getBehaviour('link')->getForeignModel($destination);
            foreach($link->getRelations() as $relation) {
                if($relation->getDestination() == $foreign->getName()) {
                    break;
                }
            }

            $relation_property_name = String::pluralize($relation->getDestinationAlias());
            $model_class = $foreign->getModelClass();
            $result .= <<<PROPERTY
    /**
     * @var {$model_class}[]
     */
    protected $$relation_property_name;


PROPERTY;
        }


        return $result;
    }

    public function renderGetterAndSetter()
    {

        $reference = $this->reference;

        $property_name = String::pluralize($reference->getReferencedBy());
        $getter = 'get' . String::convertToCamelCase($property_name);

        $source = $this->schema->getModel($reference->getSource());
        $destination = $this->schema->getModel($reference->getDestination());

        $finder = array();
        foreach($reference->getProperties() as $property) {
            $finder[] = "'" . $property->getName() . "' => \$this->" . $destination->getProperty($property->getForeignName())->getGetter() . "(),";
        }
        $finder = implode(PHP_EOL.'                ', $finder);

        $source_model_class = $source->getModelClass();
        $source_name = $reference->getSource();
        $result = <<<PROPERTY
    /**
     * get $property_name
     * @return $source_model_class
     */
    public function $getter()
    {
        if(is_null(\$this->$property_name)) {
            \$this->$property_name = \$this->getRepository()->getMaster()->findAll('$source_name', array(
                $finder
            ));
        }
        return \$this->$property_name;
    }


PROPERTY;

        if($this->schema->getModel($source_name)->hasBehaviour('link')) {
            $link = $this->schema->getModel($source_name);
            $foreign = $link->getBehaviour('link')->getForeignModel($destination);
            foreach($link->getRelations() as $relation) {
                if($relation->getDestination() == $foreign->getName()) {
                    break;
                }
            }

            $relation_property_name = String::pluralize($relation->getDestinationAlias());
            $relation_getter = 'get' . String::convertToCamelCase($relation_property_name);

            $link_getter = 'get' . String::convertToCamelCase($relation->getDestinationAlias());

            $foreign_model_class = $foreign->getModelClass();
            $result .= <<<PROPERTY
    /**
     * get $relation_property_name
     * @return {$foreign_model_class}[]
     */
    public function $relation_getter()
    {
        if(is_null(\$this->$relation_property_name)) {
            \$this->$relation_property_name = array();
            foreach(\$this->$getter() as \$link) {
                \$this->{$relation_property_name}[] = \$link->{$link_getter}();
            }
        }
        return \$this->$relation_property_name;
    }


PROPERTY;
        }

        return $result;
    }

    public function hasRelation()
    {
        return !is_null($this->property->getRelation());
    }

    public function renderRelationProperty()
    {
        $property = $this->property;
        $relation = $property->getRelation();

        $foreignModel = $this->schema->getModel($relation->getDestination());

        $name = $relation->getDestinationAlias() ? $relation->getDestinationAlias() : $foreignModel->getName();
        $model_class = $foreignModel->getModelClass();

        return <<<RELATION
    /**
     * $name property
     * @var $model_class
     */
    protected $$name;


RELATION;
    }

    public function getUsages()
    {
        $usages = array();
        return $usages;
    }    
}