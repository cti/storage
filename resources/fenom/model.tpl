<?php

namespace Storage\Model;

use Storage\Repository\{$model->getClassName()}Repository as Repository;
use \Storage\Model\ModuleBase as Module;

{include 'blocks/comment.tpl'}

class {$model->getClassName()}Base
{
{foreach $model->getProperties() as $property}
{include 'model/property.tpl'}
{/foreach}
{foreach $model->getReferences() as $reference}
// references are temporary disabled
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
{include 'model/setter_and_getter.tpl'}

{/foreach}
{foreach $model->getInReferences() as $reference}
{include 'model/reference.tpl'}

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
        if ($this->_unsaved) {
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