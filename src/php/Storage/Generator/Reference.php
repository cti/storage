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

        $finder = array();
        foreach($reference->properties as $property) {
            $finder[] = "'$property->name' => \$this->" . $this->schema->getModel($reference->destination)->getProperty($property->foreignName)->getter . "(),";
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
    return $result;

    $type = $property->readonly ? 'protected' : 'public';

            $result .= <<<PROPERTY
    /**
     * Set $property->name
     * @param $property->type
     * @return $model->model_class
     */
    $type function $property->setter($$property->name)
    {
        if(!isset(\$this->_changes['$property->name'])) {
            \$this->_changes['$property->name'] = array(
                'old' => \$this->$property->name,
            );
        }
        \$this->_changes['$property->name']['new'] = $$property->name;
        \$this->$property->name = $$property->name;
        return \$this;
    }


PROPERTY;

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