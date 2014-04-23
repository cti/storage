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
        $comment = $property->type == 'virtual' ? $property->virtual_name : $property->comment;
        $type = $property->type == 'virtual' ? $property->model->model_class : $property->type;
        return <<<PROPERTY
    /**
     * $comment
     * @var $type
     */
    protected $$property->name;


PROPERTY;
    }

    public function renderGetterAndSetter()
    {

        $model = $this->model;
        $property = $this->property;
        $result = <<<PROPERTY
    /**
     * $property->comment
     * @return $property->type
     */
    public function $property->getter()
    {
        return \$this->$property->name;
    }


PROPERTY;

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

    public function renderRelation()
    {
        $model = $this->model;
        $property = $this->property;
        $relation = $property->relation;

        $foreignModel = $this->schema->getModel($relation->destination);

        $name = $relation->destination_alias ? $relation->destination_alias : $foreignModel->name;
        $getter = 'get' . String::convertToCamelCase($name);
        $setter = 'set' . String::convertToCamelCase($name);

        $clear = array();
        $finder = array();
        $reader = array();

        foreach($relation->properties as $property) {

            $clear[] = "\$this->" . $property->setter . '(null);';

            $reader[] = sprintf(
                "\$this->%s($%s->%s());",
                $property->setter,
                $name,
                $foreignModel->getProperty($property->foreignName)->getter
            );

            $finder[] = "'" . $property->foreignName . "' => \$this->" . $property->getter . '()';
        }

        $finder = implode(',' . PHP_EOL.'                ', $finder);
        $reader = implode(PHP_EOL . '            ', $reader);
        $clear = implode(PHP_EOL . '        ', $clear);


        return <<<RELATION
    /**
     * Get $name
     * @return $foreignModel->model_class
     */
    public function $getter()
    {
        if(!\$this->$name) {
            \$master = \$this->getRepository()->getMaster();
            \$this->$name = \$master->findByPk('$relation->destination', array(
                $finder
            ));
        }
        return \$this->$name;
    }

    /**
     * Set $name
     * @param $foreignModel->model_class $$name
     * @return $model->model_class
     */
    public function $setter($foreignModel->class_name $$name = null)
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
            $model = $this->schema->getModel($this->property->relation->destination);
            $usages[] = sprintf('use %s as %s;', $model->model_class, $model->class_name);
        }
        return $usages;
    }    
}