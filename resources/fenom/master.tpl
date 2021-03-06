<?php

namespace Storage;

use Cti\Di\Manager;
use Cti\Storage\Adapter\DBAL;
use OutOfRangeException;
use Storage;

{include 'blocks/comment.tpl'}

class Master
{
    /**
     * @inject
     * @var Manager
     */
    protected $manager;

    /**
     * @inject
     * @var DBAL
     */
    protected $database;

    /**
     * create new instance
     * @param  string $name model name
     * @param  array  $data
     * @return mixed
     */
    public function create($name, $data)
    {
        return $this->getRepository($name)->create($data);
    }

    /**
     * findOne instance
     * @param  string $name model name
     * @param  array  $params
     * @return mixed
     */
    public function findOne($name, $params)
    {
        return $this->getRepository($name)->findOne($params);
    }

    /**
     * findMany instance
     * @param  string $name model name
     * @param  array  $params
     * @return mixed
     */
    public function findMany($name, $params)
    {
        return $this->getRepository($name)->findMany($params);
    }

{foreach $schema->getModels() as $model}
    /**
     * Get {$model->getClassName()} repository
     * @return {$model->getRepositoryClass()} 
     */
    public function get{$model->getClassNameMany()}()
    {
        return $this->manager->get('{$model->getRepositoryClass()}');
    }

{/foreach}
    /**
     * Get repository by model nick
     * @param  string $name
     * @return mixed
     * @throws OutOfRangeException
     */
    public function getRepository($name)
    {
        $map = array(
{foreach $schema->getModels() as $model}
            '{$model->getName()}' => 'get{$model->getClassNameMany()}',
{/foreach}
        );
        if (!isset($map[$name])) {
            throw new OutOfRangeException("Model $name was not defined");
        }
        $method = $map[$name];
        return $this->$method();
    }

    /**
     * @return DBAL
     */
    public function getDatabase()
    {
        return $this->database;
    }
}