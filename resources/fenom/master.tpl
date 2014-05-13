<?php

namespace Storage;

use OutOfRangeException;

{include 'blocks/comment.tpl'}
class Master
{
    /**
     * @inject
     * @var \Cti\Di\Manager
     */
    protected $manager;

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

}