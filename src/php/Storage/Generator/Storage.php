<?php

namespace Storage\Generator;

use Storage\Component\Model;
use Storage\Component\Property;

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
            'namespace Storage;',
            'use OutOfRangeException;',
            $this->getClassComment() . PHP_EOL . 'class Storage'.PHP_EOL,
            ));

        $result .= '{' . PHP_EOL;

        $result .= $this->renderHeader();

        foreach($this->schema->getModels() as $model) {
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
        $name = $model->getName();
        $repository_class = $model->getRepositoryClass();

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
        foreach($this->schema->getModels() as $model) {
            $map .= "            '" . $model->getName()."' => 'get" . $model->class_name_many."',";
        }

        $dump = $this->schema->getDump();

        return <<<FOOTER
    /**
     * Get repository by model nick
     * @param  string \$name 
     * @return mixed
     * @throws OutOfRangeException
     */
    public function getRepository(\$name)
    {
        \$map = array(
$map
        );
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
            \$this->manager->inject(\$this->schema);
        }
        return \$this->schema;
    }
FOOTER;
    }
}