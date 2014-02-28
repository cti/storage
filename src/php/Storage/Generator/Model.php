<?php

namespace Storage\Generator;

use Storage\Component\Property;

class Model
{
    /**
     * @var Storage\Component\Model
     */
    public $model;

    /**
     * @inject
     * @var Application\View
     */
    public $view;

    /**
     * @inject
     * @var Application\Locator
     */
    public $locator;

    public function __toString()
    {
        $model = $this->model;
        $result = implode(PHP_EOL . PHP_EOL, array(
            '<?php',
            'namespace Storage\Model;',
            'use Storage\Repository\\' . $model->getClassName() . 'Repository;',
            $this->getClassComment(). PHP_EOL . 'class '.$model->getClassName() . 'Base'.PHP_EOL,
            ));
        $result .= '{' . PHP_EOL;

        foreach($model->getProperties() as $property)
        {
            $result .= $this->renderPropertyDescription($property);
        }

        $result .= $this->renderBase();

        foreach($model->getProperties() as $property)
        {
            $result .= $this->renderPropertyGetter($property);
            if($property->setter) {
                $result .= $this->renderPropertySetter($property);
            }
        }

        $result .= $this->renderFinal();

        $result .= '}';

        return $result;
    }

    public function renderBase()
    {
        $class = $this->model->getClassName();

        return <<<BASE
    /**
     * model repository
     * @var Base\Repository\\$class
     */
    protected \$_repository;

    /**
     * changes container
     * @var array
     */
    protected \$_changes = array();

    public function __construct({$class}Repository \$repository, \$data = array())
    {
        \$this->_repository = \$repository;
        foreach(\$data as \$k => \$v) {
            \$this->\$k = \$v;
        }
    }


BASE;
    }

    public function renderPropertyDescription(Property $property)
    {
        return <<<PROPERTY
    /**
     * $property->comment
     * @var $property->type
     */
    protected $$property->name;


PROPERTY;
    }

    public function renderPropertyGetter(Property $property)
    {
        return <<<GETTER
    /**
     * $property->comment
     * @param $property->type
     */
    public function $property->getter()
    {
        return \$this->$property->name;
    }


GETTER;
    }

    public function renderPropertySetter(Property $property)
    {
        return <<<SETTER
    /**
     * Set $property->name
     * @param $property->type
     */
    public function $property->setter($$property->name)
    {
        if(!isset(\$this->_changes['$property->name'])) {
            \$this->_changes['$property->name'] = array(
                'old' => \$this->$property->name,
            );
        }
        \$this->_changes['$property->name']['new'] = $$property->name;
        \$this->$property->name = $$property->name;
    }


SETTER;
    }

    public function getMaxPropertyLength()
    {
        if(!isset($this->max)) {
            foreach($this->model->getProperties() as $property) {
                $this->max = !isset($this->max) || $this->max < strlen($property->name) ? strlen($property->name) : $this->max;
            }
        }
        return $this->max;
    }

    public function renderFinal()
    {
        $pk = '';
        foreach($this->model->getPk() as $nick) {
            $property = $this->model->getProperty($nick);
            $pk .= '            \'' . $property->name."'" . str_repeat(' ', $this->getMaxPropertyLength() - strlen($property->name)). " => \$this->".$property->getter.'(),' . PHP_EOL;
        }

        $array = '';
        foreach($this->model->getProperties() as $property) {
            $array .= '            \'' . $property->name."'" . str_repeat(' ', $this->getMaxPropertyLength() - strlen($property->name)). " => \$this->".$property->getter.'(),' . PHP_EOL;
        }
        return <<<SETTER

    /**
     * Save item in repository
     */
    public function save()
    {
        foreach(\$this->_changes as \$k => \$change) {
            if(\$change['old'] == \$change['new']) {
                unset(\$this->_changes[\$k]);
            }
        }
        if(count(\$this->_changes)) {
            \$this->_repository->save(\$this, \$this->_changes);
            \$this->_changes = array();
        }
    }

    /**
     * Delete item from repository
     */
    public function delete()
    {
        \$this->_repository->delete(\$this);
    }

    /**
     * Get primary key
     * @return array
     */
    public function getPrimaryKey()
    {
        return array(
$pk        );
    }

    /**
     * Get model properties
     * @return array
     */
    public function asArray()
    {
        return array(
$array        );
    }


SETTER;
    }

    function getClassComment()
    {
        return <<<COMMENT
/** 
 * ATTENTION! DO NOT CHANGE! THIS CODE IS REGENERATED
 * Use migrations if you want to change the result of this file
 */
COMMENT;
    }

}