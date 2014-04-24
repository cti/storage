<?php

namespace Cti\Storage\Generator;

class Repository
{
    /**
     * @var \Cti\Storage\Component\Model
     */
    public $model;

    public function __toString()
    {
        $model = $this->model;
        $result = implode(PHP_EOL . PHP_EOL, array(
            '<?php',
            'namespace Storage\Repository;',
//            sprintf('use %s as Select;', $this->model->getQueryClass()),
            $this->getClassComment() . PHP_EOL . 'class '. $model->getClassName() . 'Repository'.PHP_EOL,
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
            $fields[] = $property->getName();
        }
        $fields = "array('".implode("', '", $fields)."')";
        $class = $this->model->getClassName();

        $model_name = $this->model->getName();
        $model_class = $this->model->getModelClass();

        return <<<BASE
    /**
     * @inject
     * @var \Cti\Di\Manager
     */
    protected \$manager;

    /**
     * @inject
     * @var \Cti\Storage\Database
     */
    protected \$database;

    /**
     * @inject
     * @var \Storage\Master
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
     * @param array \$data
     * @return $model_class
     */
    public function create(\$data = array())
    {
        return \$this->manager->create('$model_class', array(
            'repository' => \$this,
            'data'       => \$data
        ));
    }

    /**
     * Get Master instance
     * @return \Storage\Master
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