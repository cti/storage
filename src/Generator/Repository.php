<?php

namespace Cti\Storage\Generator;

class Repository
{
    /**
     * @var Cti\Storage\Component\Model
     */
    public $model;

    public function __toString()
    {
        $model = $this->model;
        $result = implode(PHP_EOL . PHP_EOL, array(
            '<?php',
            'namespace Storage\Repository;',
            sprintf('use %s as Select;', $this->model->getQueryClass()),
            $this->getClassComment() . PHP_EOL . 'class '. $model->class_name . 'Repository'.PHP_EOL,
            ));
        
        $result .= '{' . PHP_EOL;
        $result .= $this->renderCti();
        $result .= '}';

        return $result;
    }

    protected function renderCti()
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
     * @var Cti\Di\Manager
     */
    protected \$manager;

    /**
     * @inject
     * @var Cti\Storage\Database
     */
    protected \$database;

    /**
     * @inject
     * @var Storage\Master
     */
    protected \$master;

    /**
     * model fields
     * @var array
     */
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
        return \$this->manager->create('$model_class', array(
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
        return \$this->manager->create('$query_class', '$model_name');
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
     * Get Master instance
     * @return Storage\Master
     */
    public function getMaster()
    {
        return \$this->master;
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