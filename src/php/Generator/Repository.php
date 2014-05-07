<?php

namespace Cti\Storage\Generator;

class Repository
{
    /**
     * @var \Cti\Storage\Component\Model
     */
    public $model;

    /**
     * @var \Cti\Di\Manager
     */
    protected $manager;

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
     * @var \Cti\Storage\Adapter\DBAL
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
            'data'       => \$data,
            'unsaved' => true,
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


    public function save($model_class \$model, \$data, \$unsaved)
    {
        if (\$unsaved) {
{$this->getPreInsertCode()}
            \$this->database->insert('{$this->model->getName()}', \$data);
        } else {
{$this->getUpdateCode()}
        }
    }

    public function delete($model_class \$model, \$unsaved)
    {
        if (\$unsaved) {
            throw new \Exception("Model $model_class can't be deleted. It is unsaved");
        }
{$this->getDeleteCode()}
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

    public function getPreInsertCode()
    {
        $code = "";
        if ($this->model->getBehaviour('id')) {
            $sq_name = 'sq_' . $this->model->getName();
            $code .= <<<INSERT
            \$id = \$this->database->fetchNextvalFromSequence('$sq_name');
            \$data['id_{$this->model->getName()}'] = \$id;
            \$model->{$this->model->getProperty('id_'.$this->model->getName())->getSetter()}(\$id);

INSERT;

        }
        if ($this->model->getBehaviour('log')) {
            $code .= <<<INSERT
            \$now = \$this->database->fetchNow();
            \$data['v_start'] = \$now;
            \$data['v_end'] = '9999-12-31 23:59:59';
            \$model->setVStart(\$data['v_start']);
            \$model->setVEnd(\$data['v_end']);
INSERT;

        }
        return $code;
    }

    public function getUpdateCode()
    {
        if ($this->model->getBehaviour('log')) {
            return <<<UPDATE
            \$now = \$this->database->fetchNow();
            /**
             * Update dt_end in old version
             */
            \$this->database->update('{$this->model->getName()}', array('v_end' => \$now), \$model->getPrimaryKey());

            /**
             * Insert new version into database
             */
            \$model->setVStart(\$now);
            \$this->database->insert('{$this->model->getName()}', \$model->asArray());
UPDATE;

        } else {
            return <<<UPDATE
            /**
             * Data contains only changes
             */
            \$this->database->update('{$this->model->getName()}', \$data, \$model->getPrimaryKey());
UPDATE;
        }
    }
    public function getDeleteCode()
    {
        if ($this->model->getBehaviour('log')) {
            return <<<DELETE
        \$now = date('Y-m-d H:i:s', strtotime(\$this->database->fetchNow()));
        \$this->database->update('{$this->model->getName()}', array('v_end' => \$now), \$model->getPrimaryKey());
        \$model->setVEnd(\$now);
DELETE;
        } else {
            return <<<DELETE
        \$this->database->delete('{$this->model->getName()}', \$model->getPrimaryKey());
DELETE;

        }
    }
}