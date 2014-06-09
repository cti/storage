<?php

namespace Storage\Model;

{include 'php/model/model_use.tpl'}
use Storage\Repository\{$model->getClassName()}Repository as Repository;

{include 'php/blocks/comment.tpl'}

class {$model->getClassName()}Base
{
{foreach $model->getProperties() as $property}
{include 'php/model/property.tpl'}
{/foreach}
{foreach $model->getReferences() as $reference}
{include 'php/model/property_reference.tpl'}
{/foreach}
    /**
     * model repository
     * @var Repository
     */
    protected $_repository;

    /**
     * changes container
     * @var array
     */
     protected $_changes = array();

    /**
     * saved state
     * @var boolean
     */
    protected $_saved = false;

    /**
     * {$model->getClassName()} constructor
     * @param Repository $repository
     * @param array $data
     * @param boolean $saved
     */
    public function __construct(Repository $repository, $data = array(), $saved = false)
    {
        $this->_repository = $repository;
        foreach($data as $k => $v) {
            $this->$k = $v;
        }
        $this->_saved = $saved;
    }

{foreach $model->getProperties() as $property}
{include 'php/model/setter_and_getter.tpl'}

{/foreach}
{foreach $model->getInReferences() as $reference}
{include 'php/model/reference.tpl'}

{/foreach}
    /**
     * Get model repository
     * @return Repository 
     */
    public function getRepository()
    {
        return $this->_repository;
    }

    /**
     * Save item in repository
     * @return {$model->getClassName()} 
     */
    public function save()
    {
        $changes = array();
        if (!$this->_saved) {
            $changes = $this->asArray();
        } else {
            foreach($this->_changes as $k => $change) {
                if ($change['old'] != $change['new']) {
                    $changes[$k] = $change['new'];
                }
            }
        }
        if (count($changes)) {
            $this->getRepository()->save($this, $changes, $this->_saved);
            $this->_changes = array();
        }
        $this->_saved = true;
        return $this;
    }

    /**
     * Delete model from database and repository storage
     */
    public function delete()
    {
        if (!$this->_saved) {
            throw new \Exception("Model {$model->getName()} is saved. Delete forbidden.");
        }
        $this->getRepository()->delete($this);
    }

    /**
     * Get model primary key
     * @return array
     */
    public function getPrimaryKey()
    {
        return array(
{foreach $model->getPk() as $fieldName}
            '{$fieldName}' => $this->{$model->getProperty($fieldName)->getGetter()}(),
{/foreach}
        );
    }

    /**
     * Convert model to array
     * @return array
     */
    public function asArray()
    {
        return array(
{foreach $model->getProperties() as $property}
            '{$property->getName()}' => $this->{$property->getGetter()}(),
{/foreach}
        );
    }
}