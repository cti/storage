<?php

namespace Nekufa\Storage\Generator;

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
            'namespace Nekufa\Storage\Repository;',
            sprintf('use %s as Select;', $this->model->getQueryClass()),
            $this->getClassComment() . PHP_EOL . 'class '. $model->class_name . 'Repository'.PHP_EOL,
            ));
        
        $result .= '{' . PHP_EOL;
        $result .= $this->renderNekufa();
        $result .= '}';

        return $result;
    }

    protected function renderNekufa()
    {
        $fields = array();
        foreach($this->model->getProperties() as $property)
        {
            $fields[] = $property->name;
        }
        $fields = "array('".implode("', '", $fields)."')";
        $class = $this->model->class_name;

        $model_name = $this->model->name;
        $model_class = $this->model->model_class;
        $query_class = $this->model->getQueryClass();

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

    /**
     * select objects
     * @return $query_class
     */
    public function select()
    {
        return \$this->di->create('$query_class', '$model_name');
    }

    /**
     * select one object
     * @return $query_class
     */
    public function selectOne()
    {
        return \$this->select()->setFetchMode(Select::FETCH_ONE);
    }

    /**
     * Get Storage instance
     * @return Storage\Storage
     */
    public function getStorage()
    {
        return \$this->storage;
    }

BASE;
    }

    function getClassComment()
    {
        return <<<COMMENT
/** 
 * ATTENTION! DO NOT CHANGE! THIS CODE IS REGENERATED
 */
COMMENT;
    }
}