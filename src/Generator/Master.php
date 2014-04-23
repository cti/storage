<?php

namespace Cti\Storage\Generator;

use Cti\Storage\Component\Model;
use Cti\Storage\Component\Property;

class Master
{
    /**
     * @var Cti\Storage\Schema
     */
    public $schema;

    public function __toString()
    {
        $result = implode(PHP_EOL . PHP_EOL, array(
            '<?php',
            'namespace Storage;',
            'use OutOfRangeException;',
            $this->getClassComment() . PHP_EOL . 'class Master'.PHP_EOL,
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
     * @var Cti\Di\Manager
     */
    protected \$manager;


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
        return \$this->manager->get('$repository_class');
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
            $map .= "            '" . $model->name."' => 'get" . $model->class_name_many."'," . PHP_EOL;
        }

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

FOOTER;
    }
}