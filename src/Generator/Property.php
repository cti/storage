<?php

namespace Cti\Storage\Generator;

use Cti\Core\String;

class Property
{
    /**
     * @inject
     * @var \Cti\Storage\Schema
     */
    public $schema;

    /**
     * @var \Cti\Storage\Component\Property
     */
    public $property;

    /**
     * @var \Cti\Storage\Component\Model
     */
    public $model;

    public function renderDescription()
    {
        $property = $this->property;
        $comment = $property->getComment();
        $type = $property->getType();
        $name = $property->getName();
        return <<<PROPERTY
    /**
     * $comment
     * @var $type
     */
    protected $$name;


PROPERTY;
    }

    public function renderGetterAndSetter()
    {

        $model = $this->model;
        $property = $this->property;
        $comment = $property->getComment();
        $type = $property->getType();
        $getter = $property->getGetter();
        $name = $property->getName();
        $access = $property->getReadonly() ? 'protected' : 'public';
        $model_class = $model->getModelClass();
        $setter = $property->getSetter();
        $result = <<<PROPERTY
    /**
     * $comment
     * @return $type
     */
    public function $getter()
    {
        return \$this->$name;
    }


PROPERTY;


        $result .= <<<PROPERTY
    /**
     * Set $name
     * @param $type
     * @return $model_class
     */
    $access function $setter($$name)
    {
        if(!isset(\$this->_changes['$name'])) {
            \$this->_changes['$name'] = array(
                'old' => \$this->$name,
            );
        }
        \$this->_changes['$name']['new'] = $$name;
        \$this->$name = $$name;
        return \$this;
    }


PROPERTY;

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

    public function renderRelation()
    {
        $model = $this->model;
        $property = $this->property;
        $relation = $property->getRelation();

        $foreignModel = $this->schema->getModel($relation->getDestination());

        $name = $relation->getDestinationAlias() ? $relation->getDestinationAlias() : $foreignModel->getName();
        $getter = 'get' . String::convertToCamelCase($name);
        $setter = 'set' . String::convertToCamelCase($name);

        $clear = array();
        $finder = array();
        $reader = array();

        foreach($relation->getProperties() as $property) {

            $clear[] = "\$this->" . $property->getSetter() . '(null);';

            $reader[] = sprintf(
                "\$this->%s($%s->%s());",
                $property->getSetter(),
                $name,
                $foreignModel->getProperty($property->getForeignName())->getGetter()
            );

            $finder[] = "'" . $property->getForeignName() . "' => \$this->" . $property->getGetter() . '()';
        }

        $finder = implode(',' . PHP_EOL.'                ', $finder);
        $reader = implode(PHP_EOL . '            ', $reader);
        $clear = implode(PHP_EOL . '        ', $clear);


        $foreign_model_class = $foreignModel->getModelClass();
        $destination = $relation->getDestination();
        $model_class = $model->getModelClass();
        $foreign_class_name = $foreignModel->getClassName();
        return <<<RELATION
    /**
     * Get $name
     * @return $foreign_model_class
     */
    public function $getter()
    {
        if(!\$this->$name) {
            \$master = \$this->getRepository()->getMaster();
            \$this->$name = \$master->findByPk('$destination', array(
                $finder
            ));
        }
        return \$this->$name;
    }

    /**
     * Set $name
     * @param $foreign_model_class $$name
     * @return $model_class
     */
    public function $setter($foreign_class_name $$name = null)
    {
        $clear
        if($$name) {
            $reader
        }
        return \$this;
    }


RELATION;
    }

    public function getUsages()
    {
        $usages = array();
        if($this->hasRelation()) {
            $model = $this->schema->getModel($this->property->getRelation()->getDestination());
            $usages[] = sprintf('use %s as %s;', $model->getModelClass(), $model->getClassName());
        }
        return $usages;
    }    
}