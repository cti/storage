<?php

namespace Storage\Generator;

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

    /**
     * @inject
     * @var Di\Manager
     */
    protected $manager;

    public function __toString()
    {
        try {

            $model = $this->model;

            $properties = array();
            foreach($model->getProperties() as $property) {
                $properties[] = $this->manager->create('Storage\Generator\Property', array(
                    'property' => $property
                ));
            }

            $header = array(
                '<?php',
                'namespace Storage\\Model;',
                $this->appendUsage('use Storage\\Repository\\' . $model->class_name . 'Repository as Repository;'),
                $this->getClassComment(). PHP_EOL . 'class '.$model->class_name . 'Base'.PHP_EOL,
            );

            $result = implode(PHP_EOL . PHP_EOL, $header) . '{' . PHP_EOL;

            foreach($properties as $property) {
                $result .= $property->renderDescription();
            }

            $result .= $this->renderHeader();

            foreach($properties as $property) {
                $result .= $property->renderGetterAndSetter();
            }

            foreach($properties as $property) {
                if($property->hasRelation()) {
                    $result .= $property->renderRelation();
                }
            }

            $result .= $this->renderFinal() . '}';
        } catch(\Exception $e) {
            return $e->getMessage();

        }

        return $result;
    }

    public function appendUsage($usage)
    {
        $result = array();
        // foreach($this->model->one as $property) {
        //     if(!isset($result[$property->model->class_name])) {
        //         $result[$property->model->class_name] = 'use ' . $property->model->model_class . ' as ' . $property->model->class_name.';';
        //     }
        // }

        // foreach($this->model->links as $link) {
        //     $foreign = $link->getForeignModel($this->model);
        //     $result[$foreign->class_name] = 'use ' . $foreign->model_class . ' as ' . $foreign->class_name.';';
        // }
        // if(count($result)) {
        //     array_unshift($result, '');
        // }
        return $usage . implode(PHP_EOL, $result);
    }

    public function renderHeader()
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

    public function renderPropertyDescription($property)
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

    public function renderProperty($property)
    {

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

        return $result;
    }

    public function renderVirtualProperty($property)
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

        $model = $this->model;

        return <<<FINAL

    /**
     * Get model repository
     * @return $model->repository_class
     */
    public function getRepository()
    {
        return \$this->_repository;
    }

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
            \$this->getRepository()->save(\$this, \$changes);
            \$this->_changes = array();
        }
    }

    /**
     * Delete item from repository
     */
    public function delete()
    {
        \$this->getRepository()->delete(\$this);
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


FINAL;
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