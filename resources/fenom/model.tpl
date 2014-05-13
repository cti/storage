<?php

namespace Storage\Model;

use Storage\Repository\{$model->getClassName()}Repository as Repository;
use \Storage\Model\ModuleBase as Module;

{include 'blocks/comment.tpl'}

class {$model->getClassName()}Base
{
{foreach $model->getProperties() as $property}
    /**
     * {$property->getComment()}

     * @var {$property->getType()}

     */
     protected ${$property->getName()};

{if $property->getRelation()?}
{var $relation = $property->getRelation()}
{var $foreignModel = $schema->getModel($relation->getDestination())}
{var $name = $relation->getDestinationAlias() ? $relation->getDestinationAlias() : $foreignModel->getName()}
    /**
     * {$name} property
     * @var {$foreignModel->getClassName()}

     */
     protected ${$name};
{/if}
{/foreach}
{foreach $model->getReferences() as $reference}
{var $source = $schema->getModel($reference->getSource())}
{var $rb = $reference->getReferencedBy()}
    /**
     * @var {$source->getModelClass()}[]

     */
     protected ${$rb|pluralize};
{/foreach}

    /**
     * model repository
     * @var {$model->getRepositoryClass()}

     */
    protected $_repository;

    /**
     * changes container
     * @var array
     */
     protected $_changes = array();

    /**
     * unsaved state
     * @var boolean
     */
    protected $_unsaved = false;

    /**
     * create new {$model->getName()}

     * @param {$model->getRepositoryClass()} $repository
     * @param array $data
     * @param boolean $unsaved
     */
    public function __construct(Repository $repository, $data = array(), $unsaved = false)
    {
        $this->_repository = $repository;
        foreach($data as $k => $v) {
            $this->$k = $v;
            $this->_unsaved = $unsaved;
        }
    }

{foreach $model->getProperties() as $property}
{var $name = $property->getName()}
    /**
     * {$property->getComment()}

     * @return {$property->getType()}

     */
    public function {$property->getGetter()}()
    {
       return $this->{$name};
    }

    /**
     * Set {$name}

     * @param {$property->getType()}

     * @return {$model->getModelClass()}

     */
    public function {$property->getSetter()}(${$name})
    {
        if (!isset($this->_changes['{$name}'])) {
            $this->_changes['{$name}'] = array(
                'old' => $this->{$name}
            );
        }
        $this->_changes['{$name}']['new'] = ${$name};
        $this->{$name} = ${$name};
        return $this;
    }

{if $property->getRelation()?}
{var $relation = $property->getRelation()}
{var $foreignModel = $schema->getModel($relation->getDestination())}
{var $name = $relation->getDestinationAlias() ? $relation->getDestinationAlias() : $foreignModel->getName()}
    /**
     * Get {$name}

     * @return {$foreignModel->getClassName()}

     */
    public function get{$name|camelcase}()
    {
        if (!$this->{$name}) {
            $master = $this->getRepository()->getMaster();
            $this->{$name} = $master->getRepository('{$relation->getDestination()}')->findByPk(array(
    {foreach $relation->getProperties() as $relationProperty}
                '{$relationProperty->getForeignName()}' => $this->{$relationProperty->getGetter()}(),
    {/foreach}
            ));
        }
        return $this->{$name};
    }

    /**
     * Set {$name}

     * @param {$foreignModel->getClassName()} ${$name}

     * @return {$model->getModelClass()}

     */
    public function set{$name|camelcase}({$foreignModel->getClassName()} ${$name} = null)
    {
    {foreach $relation->getProperties() as $relationProperty}

        $this->{$relationProperty->getSetter()}(null);
    {/foreach}

        if (${$name}) {
{foreach $relation->getProperties() as $relationProperty}
            $this->{$relationProperty->getSetter()}(${$name}->{$foreignModel->getProperty($relationProperty->getForeignName())->getGetter()}());
{/foreach}
        }
        return $this;
    }

{/if}
{/foreach}

    /**
     * Get model repository
     * @return {$model->getRepositoryClass()}
     */
    public function getRepository()
    {
        return $this->_repository;
    }

    /**
     * Save item in repository
     * @return {$model->getModelClass()}

     */
    public function save()
    {
        $changes = array();
        if ($this->_unsaved === true) {
            $changes = $this->asArray();
        } else {
            foreach($this->_changes as $k => $change) {
                if ($change['old'] != $change['new']) {
                    $changes[$k] = $change['new'];
                }
            }
        }
        if (count($changes)) {
            $this->getRepository()->save($this, $changes, $this->_unsaved);
            $this->_changes = array();
        }
        $this->_unsaved = false;
        return $this;
    }

    /**
     * Delete model from database and repository storage
     */
    public function delete()
    {
        if (!$this->_unsaved) {
            throw new \Exception("Model {$model->getName()} is unsaved. Delete forbidden.");
        }
        $this->getRepository()->delete($this);
    }

    /**
     * Get primary key of model
     */
    public function getPrimaryKey()
    {
        return array(
{foreach $model->getPk() as $fieldName}
            '{$fieldName}' => $this->{$model->getProperty($fieldName)->getGetter()}(),
{/foreach}
        );
    }

    public function asArray()
    {
        return array(
{foreach $model->getProperties() as $property}
            '{$property->getName()}' => $this->{$property->getGetter()}(),
{/foreach}
        );
    }
}