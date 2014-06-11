<?php

namespace Storage\Repository;

{include 'model/model_use.tpl'}
use Cti\Storage\Adapter\DBAL;

{include 'blocks/comment.tpl'}

class {$model->getClassName()}Repository
{
    /**
     * @inject
     * @var \Cti\Di\Manager
     */
    protected $manager;

    /**
     * @inject
     * @var DBAL
     */
    protected $database;

    /**
     * @inject
     * @var \Storage\Master
     */
    protected $master;

    /**
     * model fields
     * @var array
     */
    protected $fields = array();

    /**
     * Container for loaded or saved models
     * @var {$model->getClassName()}[]
     */
    protected $map = array();

    public function __construct()
    {
        $this->fields = array_flip(array('{implode("', '", $fields)}'));
    }

    /**
     * Create new {$model->getName()} instance
     * @param array $data
     * @return {$model->getClassName()}

     */
    public function create($data = array())
    {
        return $this->manager->create('{$model->getModelClass()}', array(
            'repository' => $this,
            'data' => $data,
            'saved' => false,
        ));
    }
{var $link = $model->getBehaviour('link')}
{if $link?}
{var $list = array_values($link->getList())}
{var $firstModel = $list[0]}
{var $secondModel = $list[1]}
    /**
     * Create new {$model->getName()} instance
     * @param {$firstModel->getClassName()} ${$firstModel->getName()}

     * @param {$secondModel->getClassName()} ${$secondModel->getName()}

     * @return {$model->getClassName()}

     */
    public function createLink({$firstModel->getClassName()} ${$firstModel->getName()}, {$secondModel->getClassName()} ${$secondModel->getName()}, $data = array())
    {
{foreach $model->getReferences() as $reference}
{var $remoteModel = $schema->getModel($reference->getDestination())}
{foreach $reference->getProperties() as $property}
{var $remoteProperty = $remoteModel->getProperty($property->getForeignName())}
        $data['{$property->getName()}'] = ${$remoteModel->getName()}->{$remoteProperty->getGetter()}();
{/foreach}
{/foreach}
        return $this->create($data);
    }
{/if}

    /**
     * Get Master instance
     * @return \Storage\Master
     */
    public function getMaster()
    {
        return $this->master;
    }

{var $name = $model->getName()}
    /**
     * Register model in repository map
     * @param {$model->getClassName()} ${$name}

     */
    public function registerModel({$model->getClassName()} ${$name})
    {
        $this->map[$this->makeKey(${$name})] = ${$name};
    }

    /**
     * Delete model from repository map
     * @param {$model->getClassName()} ${$name}

     */
    public function unregisterModel({$model->getClassName()} ${$name})
    {
        unset($this->map[$this->makeKey(${$name})]);
    }

    public function getModelFromMap($key)
    {
        return $this->map[$key];
    }

    public function keyExists($key)
    {
        return !empty($this->map[$key]);
    }

    /**
     * Save model into database and repository registry
     * @param {$model->getClassName()} $model
     * @param array $data
     * @param boolean @saved
     */
    public function save({$model->getClassName()} $model, $data, $saved)
    {
        if (!$saved) {
{if $model->getBehaviour('id')?}
            $id = $this->database->fetchNextvalFromSequence('sq_{$model->getName()}');
            $data['id_{$model->getName()}'] = $id;
            $model->{$model->getProperty("id_{$model->getName()}")->getSetter()}($id);
{/if}
{if $model->getBehaviour('log')?}
            $now = $this->database->fetchNow();
            $data['v_start'] = $now;
            $data['v_end'] = '9999-12-31 23:59:59';
            $model->setVStart($data['v_start']);
            $model->setVEnd($data['v_end']);
{/if}
            $this->database->insert('{$model->getName()}', $data);
        } else {
{if $model->getBehaviour('log')?}
            $now = $this->database->fetchNow();
            /**
             * Update dt_end in old version
             * 2 models can't be in one second, and we will finish old,version in past time
             * And new version will have current time in start time
             */
            $secondBefore = date('Y-m-d H:i:s', strtotime($now) - 1);
            $this->database->update('{$model->getName()}', array('v_end' => $secondBefore), $model->getPrimaryKey());
            /**
             * Insert new version into database
             */
            $model->setVStart($now);
            $this->database->insert('{$model->getName()}', $model->asArray());
            /**
             * Remove old model from map, key can be another
             */
            $old_key = array_search($model, $this->map);
            unset($this->map[$old_key]);
{else}
            $this->database->update('{$model->getName()}', $data, $model->getPrimaryKey());
{/if}
        }
        $this->registerModel($model);
    }

    /**
     * @param {$model->getClassName()} $model
     */
    public function delete({$model->getClassName()} $model)
    {
        $this->unregisterModel($model);
{if $model->getBehaviour('log')?}
        $now = date('Y-m-d H:i:s', strtotime($this->database->fetchNow()));
        $this->database->update('{$model->getName()}', array('v_end' => $now), $model->getPrimaryKey());
        $model->setVEnd($now);
{else}

        $this->database->delete('{$model->getName()}', $model->getPrimaryKey());
{/if}
    }

    /**
     * @param mixed ${$model->getName()}

     * @return String
     */
    public function makeKey(${$model->getName()})
    {
        if (is_array(${$model->getName()})) {
            return implode(':', array(
{foreach $model->getPk() as $fieldName}
                ${$model->getName()}['{$fieldName}'],
{/foreach}
            ));
        }
        return implode(':', array(
{foreach $model->getPk() as $fieldName}
            ${$model->getName()}->{$model->getProperty($fieldName)->getGetter()}(),
{/foreach}
        ));
    }
{var $log = $model->getBehaviour('log')}

    /**
     * @param array $params
     * @param String $mode
     * @return {$model->getClassName()}[]
     */
    public function find($params, $mode = 'many')
    {
        if(isset($params['query'])) {
            $query = $params['query'];
            $queryParams = isset($params['condition']) ? $params['condition'] : array();

        } else {
            $query = "SELECT * from {$model->getName()} where ";
            $where = array("1=1");
            $queryParams = array();

            if (isset($params['condition'])) {
                foreach($params['condition'] as $key => $value) {
                    $where[] = "$key = :$key";
                    $queryParams[$key] = $value;
                }
            }
{if $log?}
            $where[] = ":_version_date between v_start and v_end";
            $queryParams['_version_date'] = $params['version_date'];
{/if}
            $query .= implode(" AND ", $where);
        }

        if ($mode === 'many') {
            $rows = $this->database->fetchAll($query, $queryParams);
            $models = array();
            foreach($rows as $row) {
                $key = $this->makeKey($row);
                if ($this->keyExists($key)) {
                    $models[] = $this->getModelFromMap($key);
                } else {
                    $model = $this->manager->create('{$model->getModelClass()}', array(
                        'repository' => $this,
                        'data' => $row,
                        'saved' => true,
                    ));
                    $this->registerModel($model);
                    $models[] = $model;
                }
            }
            return $models;
        } elseif ($mode === 'one') {
            $row = $this->database->fetchAssoc($query, $queryParams);
            if (!$row) {
                return null;
            }
            $key = $this->makeKey($row);
            if ($this->keyExists($key)) {
                return $this->getModelFromMap($key);
            }
            $model = $this->manager->create('{$model->getModelClass()}', array(
                'repository' => $this,
                'data' => $row,
                'saved' => true,
            ));
            $this->registerModel($model);
            return $model;
        }
    }

    /**
     * @param string $where
     * @param array  $condition
     * @return {$model->getClassName()}[]
     */
    public function findWhere($where, $condition = array())
    {
        return $this->find(array(
            'query' => 'select * from {$model->getName()} where ' . $where,
            'condition' => $condition,
        ), 'many');
    }

    /**
     * @param array $condition
{if $log?}
     * @param String $version_date
{/if}
     * @return {$model->getClassName()}

     */
    public function findOne($condition = array(){if $log?}, $version_date = null{/if})
    {
{if $log?}
        if (is_null($version_date)) {
            $version_date = $this->database->fetchNow();
        }
{/if}
        return $this->find(array(
            'condition' => $condition,
{if $log?}
            'version_date' => $version_date,
{/if}
        ), 'one');
    }

    /**
     * @param array $condition
{if $log?}
     * @param String $version_date
{/if}
     * @return {$model->getClassName()}[]
     */
    public function findAll($condition = array(){if $log?}, $version_date = null{/if})
    {
{if $log?}
        /**
         * By default we will
         */
        if (is_null($version_date)) {
            $version_date = date('Y-m-d H:i:s', strtotime($this->database->fetchNow()) + 1);
        }
{/if}
        return $this->find(array(
            'condition' => $condition,
{if $log?}
            'version_date' => $version_date,
{/if}
        ), 'many');
    }

    /**
     * @param array $condition
     */
    public function findByPk($condition)
    {
        if (!$condition) {
            throw new \Exception("Need condition to find model by PK");
        }
        $where = array();
        foreach($condition as $k => $v) {
            $where[] = "$k = :$k";
        }
        $query = "select * from {$name} where " . implode(' and ', $where);
        $row = $this->database->fetchAssoc($query, $condition);
        if (!$row) {
            return null;
        } else {
            $key = $this->makeKey($row);
            if ($this->keyExists($key)) {
                return $this->getModelFromMap($key);
            } else {
                $model = $this->manager->create('{$model->getModelClass()}', array(
                    'repository' => $this,
                    'data' => $row,
                    'saved' => true,
                ));
                $this->registerModel($model);
                return $model;
            }
        }

    }

    /**
     * @return DBAL
     */
    public function getDatabase()
    {
        return $this->database;
    }
}