<?php

namespace Base\Storage\Generator;

use Base\Util\String;

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

            $usage = array(
                sprintf('use Storage\\Repository\\%sRepository as Repository;', $model->class_name)
            );

            // last contains relation properties
            $last = array();
            $properties = array();
            foreach($model->getProperties() as $property) {
                $generator = $this->manager->create('Storage\Generator\Property', array(
                    'model' => $model, 
                    'property' => $property
                ));

                foreach($generator->getUsages() as $line) {
                    if(!in_array($line, $usage)) {
                        $usage[] = $line;
                    }
                }
                if($generator->hasRelation()) {
                    $last[] = $generator;
                } else {
                    $properties[] = $generator;
                }
            }

            // add relation properties to the end
            foreach($last as $generator) {
                $properties[] = $generator;
            }

            foreach ($model->references as $reference) {
                $references[] = $this->manager->create('Storage\Generator\Reference', array(
                    'reference' => $reference
                ));
            }            

            sort($usage);

            $header = array(
                '<?php',
                'namespace Base\Storage\\Model;',
                implode(PHP_EOL, $usage),
                $this->renderComment(). PHP_EOL . 'class '.$model->class_name . 'Base'.PHP_EOL,
            );

            $result = implode(PHP_EOL . PHP_EOL, $header) . '{' . PHP_EOL;

            foreach($properties as $property) {
                $result .= $property->renderDescription();
                if($property->hasRelation()) {
                    $result .= $property->renderRelationProperty();
                }
            }

            foreach($references as $generator) {
                $result .= $generator->renderProperty();
            }

            $result .= $this->renderConstructor();

            foreach($properties as $property) {
                $result .= $property->renderGetterAndSetter();
                if($property->hasRelation()) {
                    $result .= $property->renderRelation();
                }
            }

            foreach($references as $generator) {
               $result .= $generator->renderGetterAndSetter();
            }

            $result .= $this->renderFooter() . '}';

        } catch(\Exception $e) {
            return $e->getMessage();
        }

        return $result;
    }

    function renderComment()
    {
        return <<<COMMENT
/** 
 * ATTENTION! DO NOT CHANGE! THIS CODE IS REGENERATED
 */
COMMENT;
    }

    public function renderConstructor()
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

    public function renderFooter()
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