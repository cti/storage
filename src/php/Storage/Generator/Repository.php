<?php

namespace Storage\Generator;

class Repository
{
    /**
     * @var Storage\Component\Model
     */
    public $model;

    public function __toString()
    {
        $model = $this->model;
        $result = implode(PHP_EOL . PHP_EOL, array(
            '<?php',
            'namespace Storage\Repository;',
            // 'use '.$model->model_class.' as '.$model->class_name.';',
            $this->getClassComment() . PHP_EOL . 'class '. $model->class_name . 'Repository'.PHP_EOL,
            ));
        
        $result .= '{' . PHP_EOL;
        $result .= $this->renderBase();
        $result .= '}';

        return $result;
    }

    protected function renderBase()
    {
        $fields = array();
        foreach($this->model->getProperties() as $property)
        {
            $fields[] = $property->name;
        }
        $fields = "array('".implode("', '", $fields)."')";
        $class = $this->model->class_name;
        $model_class = $this->model->model_class;
        return <<<BASE
    /**
     * @inject
     * @var Storage\Database
     */
    protected \$database;

    /**
     * @inject
     * @var Storage\Storage
     */
    protected \$storage;

    /**
     * @inject
     * @var Di\Manager
     */
    protected \$di;

    protected \$fields = array();

    public function __construct()
    {
        \$this->fields = array_flip($fields);
    }

    /**
     * Create new $class instance
     * @param  array  \$data
     * @return $model_class
     */
    public function create(\$data = array())
    {
        return \$this->di->create('$model_class', array(
            'repository' => \$this,
            'data' => \$data
        ));
    }

BASE;
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