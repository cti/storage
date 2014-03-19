<?php

namespace Storage\Generator;

use Util\String;

class Reference
{
    /**
     * @inject
     * @var Storage\Schema
     */
    public $schema;

    /**
     * @var Storage\Component\Reference
     */
    public $reference;

    public function renderGetterAndSetter()
    {

        $reference = $this->reference;

        $property_name = String::pluralize($reference->referenced_by);
        $getter = 'get' . String::convertToCamelCase($property_name);

        $destination = $this->schema->getModel($reference->destination);

        $finder = array();
        foreach($reference->properties as $property) {
            $finder[] = "'$property->name' => \$this->" . $destination->getProperty($property->foreignName)->getter . "(),";
        }
        $finder = implode(PHP_EOL.'                ', $finder);

        $result = <<<PROPERTY
    /**
     * get $property_name
     * @return $property->type
     */
    public function $getter()
    {
        if(!isset(\$this->$property_name)) {
            \$this->$property_name = \$this->getRepository()->getStorage()->findAll('$reference->source', array(
                $finder
            ));
        }
        return \$this->$property_name;
    }


PROPERTY;

        if($this->schema->getModel($reference->source)->hasBehaviour('link')) {
            $link = $this->schema->getModel($reference->source);
            $foreign = $link->getForeignModel($destination);
            foreach($link->relations as $relation) {
                if($relation->destination == $foreign->name) {
                    break;
                }
            }

            $relation_property_name = String::pluralize($relation->destination_alias);
            $relation_getter = 'get' . String::convertToCamelCase($relation_property_name);

            $link_getter = 'get' . String::convertToCamelCase($relation->destination_alias);
             
            $result .= <<<PROPERTY
    /**
     * get $relation_property_name
     * @return $foreign->model_class[]
     */
    public function $relation_getter()
    {
        if(!isset(\$this->$relation_property_name)) {
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
        return isset($this->property->relation);
    }

    public function renderRelationProperty()
    {
        $property = $this->property;
        $relation = $property->relation;

        $foreignModel = $this->schema->getModel($relation->destination);

        $name = $relation->destination_alias ? $relation->destination_alias : $foreignModel->name;

        return <<<RELATION
    /**
     * $name property
     * @var $foreignModel->model_class
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