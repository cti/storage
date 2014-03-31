<?php

namespace Nekufa\Storage\Generator;

use Nekufa\Storage\Component\Model;
use Nekufa\Storage\Component\Property;

class Storage
{
    /**
     * @var Storage\Schema
     */
    public $schema;

    public function __toString()
    {
        $result = implode(PHP_EOL . PHP_EOL, array(
            '<?php',
            'namespace Nekufa\Storage;',
            'use OutOfRangeException;',
            $this->getClassComment() . PHP_EOL . 'class Storage'.PHP_EOL,
            ));

        $result .= '{' . PHP_EOL;

        $result .= $this->renderHeader();

        foreach($this->schema->models as $model) {
            $result .= $this->renderRepositoryGetter($model);
        }
        $result .= $this->renderFooter();

        $result .= '}';

        return $result;
    }

    protected function renderHeader()
    {
        return <<<HEADER
    /**
     * @inject
     * @var Di\Manager
     */
    protected \$manager;

    /**
     * @inject
     * @var Storage\Database
     */
    protected \$database;


HEADER;
    }

    public function renderRepositoryGetter(Model $model)
    {
        $repository_class = $model->repository_class;

        return <<<GETTER
    /**
     * Get $model->class_name repository
     * @return $repository_class
     */
    public function get{$model->class_name_many}()
    {
        if(!isset(\$this->$model->name_many)) {
            \$this->$model->name_many = \$this->manager->create('$repository_class');
        }
        return \$this->$model->name_many;
    }


GETTER;
    }

    function getClassComment()
    {
        $datetime = date('d.m.Y H::s');
        return <<<COMMENT
/** 
 * ATTENTION! DO NOT CHANGE! THIS CODE IS REGENERATED
 * Use migrations if you want to change the result of this file
 */
COMMENT;
    }

    protected function renderFooter()
    {
        $map = '';
        foreach($this->schema->models as $model) {
            $map .= "            '" . $model->name."' => 'get" . $model->class_name_many."'," . PHP_EOL;
        }

        $dump = $this->schema->getDump();

        return <<<FOOTER
    /** 
     * Find record by primary key
     * @param string \$name
     * @param array  \$pk
     * @return mixed
     */
    public function findByPk(\$name, \$pk)
    {
        return \$this->getRepository(\$name)->findByPk(\$pk);
    }

    /**
     * Get repository by model nick
     * @param  string \$name 
     * @return mixed
     * @throws OutOfRangeException
     */
    public function getRepository(\$name)
    {
        \$map = array(
$map        );
        if(!isset(\$map[\$name])) {
            throw new OutOfRangeException("Model \$name was not defined");
        }
        \$method = \$map[\$name];
        return \$this->\$method();
    }

    public function getSchema()
    {
        if(!isset(\$this->schema)) {
            \$this->schema = \$this->manager->create('Storage\Schema', array(
                'dump' => '$dump'
            ));
            \$this->manager->register(\$this->schema);
        }
        return \$this->schema;
    }

FOOTER;
    }
}