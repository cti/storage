<?php

namespace Storage\Generator;

use Storage\Component\Property;

use Util\String;

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
            'namespace Storage\\Model;',
            $this->appendUsage('use Storage\\Repository\\' . $model->class_name . 'Repository as Repository;'),
            $this->getClassComment(). PHP_EOL . 'class '.$model->class_name . 'Base'.PHP_EOL,
            ));
        $result .= '{' . PHP_EOL;

        foreach($model->getProperties() as $property) {
            $result .= $this->renderPropertyDescription($property);
        }

        $result .= $this->renderBase();

        $pk = $model->getPk();

        foreach($model->getProperties() as $property) {
            if($property->type == 'virtual') {
                $result .= $this->renderVirtualProperty($property);
            } else {
                $result .= $this->renderProperty($property);
            }
        }

        foreach($model->many as $alias => $model) {
            $result .= $this->renderRelationGetters($model, $alias);
        }

        foreach($model->links as $alias => $link) {
            $result .= $this->renderLinkMethods($link, $alias);
        }

        $result .= $this->renderFinal();

        $result .= '}';

        return $result;
    }

    public function appendUsage($usage)
    {
        $result = array();
        foreach($this->model->one as $property) {
            if(!isset($result[$property->model->class_name])) {
                $result[$property->model->class_name] = 'use ' . $property->model->model_class . ' as ' . $property->model->class_name.';';
            }
        }

        foreach($this->model->links as $link) {
            $foreign = $link->getForeignModel($this->model);
            $result[$foreign->class_name] = 'use ' . $foreign->model_class . ' as ' . $foreign->class_name.';';
        }
        if(count($result)) {
            array_unshift($result, '');
        }
        return $usage . implode(PHP_EOL, $result);
    }

    public function renderBase()
    {
        $repository_class = $this->model->repository_class;
        return <<<BASE
    /**
     * model repository
     * @var $repository_class
     */
    protected \$_repository;

    /**
     * changes container
     * @var array
     */
    protected \$_changes = array();

    public function __construct(Repository \$repository, \$data = array())
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

    public function renderProperty(Property $property)
    {
        $type = $property->mapping ? 'protected' : 'public';

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

        if(!$property->readonly) {
            $result .= <<<PROPERTY
    /**
     * Set $property->name
     * @param $property->type
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
    }


PROPERTY;
        }

        return $result;
    }

    public function renderVirtualProperty(Property $property)
    {
        $name = $property->virtual_name;
        $model = $property->model;

        $finder = '';
        foreach($property->getForeignModelColumns() as $p) {
            $finder .= "                '" . $p->mapping ."' => \$this->" . $p->getter .'(), ' . PHP_EOL;
        }

        $class = $property->model->model_class;
        $class_name = $property->model->class_name;

        $setNull = '';
        $setProperties = '';
        foreach($property->getForeignModelColumns() as $p) {
            $setNull = '            $this->' . $p->setter . '(null);' . PHP_EOL;
            $setProperties = '            $this->' . $p->setter . '($'.$property->name.'->'.$property->model->getProperty($p->mapping)->getter.'());' . PHP_EOL;
        }

        return <<<VIRTUAL
    /**
     * Get $name
     * @param $model->model_class
     */
    public function $property->getter()
    {
        if(!\$this->$property->name) {
            \$this->$property->name = \$this->_repository->getStorage()->find('$model->name', array(
$finder           ));
        }
        return \$this->$property->name;
    }

    /**
     * Set $property->name
     * @param $class
     */
    public function $property->setter($class_name $$property->name = null)
    {
        \$this->$property->name = $$property->name;

        if(!$$property->name) {
$setNull        } else {
$setProperties        }
    }



VIRTUAL;
    }

    public function renderRelationGetters($property, $alias) 
    {
        $model = $property->model;

        $phpname = String::convertToCamelCase($alias);

        $finder = '';
        foreach($property->getForeignModelColumns() as $p) {
            $finder .= "            '" . $p->name . "' => \$this->" . $p->mapping . '(),' . PHP_EOL;
        }

        return <<<RELATION
    /**
     * Get $phpname
     * @return $model->model_class[]
     */
    public function get{$phpname}()
    {
        return \$this->_repository->getStorage()->find('$model->name', array(
$finder        ));

    }


RELATION;
    }

    public function renderLinkMethods($link, $alias)
    {

        $foreign = $link->getForeignModel($this->model);
        $phpname = String::convertToCamelCase($alias);
        $phpname_many = String::pluralize($phpname);

        $model_pk = $this->model->getPk();

        $combineNewValues = '';
        foreach($link->getPk() as $pk) {
            $own = in_array($pk, $model_pk);
            $property = $own ? $this->model->getProperty($pk) : $foreign->getProperty($pk);
            if(!$property->behaviour) {
                $combineNewValues .= "            '" . $property->name ."' => \$" . ($own ? 'this' : $alias).'->' . $property->getter.'(),' . PHP_EOL;
                if($own) {

                    $getConditions .= "            '" . $property->name ."' => \$this->" . $property->getter.'(),' . PHP_EOL;
                }
            }
        }

        return <<<LINK
    /**
     * Get $phpname_many
     * @return $foreign->model_class[]
     */
    public function get{$phpname_many}()
    {
        \$links = \$this->_repository->getStorage()->find('$link->name', array(
$getConditions        ));
        \$result = array();
        foreach(\$links as \$link) {
            \$result[] = \$link->get{$phpname}();
        }
        return \$result;
    }

    /** 
     * Add $phpname
     * @param $foreign->model_class
     */
    public function add{$phpname}($foreign->class_name $$alias)
    {
        \$this->_repository->getStorage()->create('$link->name', array(
$combineNewValues        ));
    }


LINK;
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
            if($property->type != 'virtual') {
                $array .= '            \'' . $property->name."'" . str_repeat(' ', $this->getMaxPropertyLength() - strlen($property->name)). " => \$this->".$property->getter.'(),' . PHP_EOL;
            }
        }
        return <<<SETTER

    /**
     * Save item in repository
     */
    public function save()
    {
        \$changes = array();
        foreach(\$this->_changes as \$k => \$change) {
            if(\$change['old'] != \$change['new']) {
                \$changes[\$k] = \$change['new'];
            }
        }
        if(count(\$changes)) {
            \$this->_repository->save(\$this, \$changes);
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

    public function getMaxPropertyLength()
    {
        if(!isset($this->max)) {
            foreach($this->model->getProperties() as $property) {
                $this->max = !isset($this->max) || $this->max < strlen($property->name) ? strlen($property->name) : $this->max;
            }
        }
        return $this->max;
    }



}