<?php

namespace Storage\Repository;

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
     * @var \Cti\Storage\Adapter\DBAL
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

    public function __construct()
    {
        $this->fields = array_flip(array('{implode("', '", $fields)}'));
    }

    /**
     * Create new Person instance
     * @param array $data
     * @return {$model->getModelClass()}

     */
    public function create($data = array())
    {
        return $this->manager->create('{$model->getModelClass()}', array(
            'repository' => $this,
            'data' => $data,
            'unsaved' => true,
        ));
    }

    /**
     * Get Master instance
     * @return \Storage\Master
     */
    public function getMaster()
    {
        return $this->master;
    }

    public function save({$model->getModelClass()} $model, $data, $unsaved)
    {
        if ($unsaved) {
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
             */
            $this->database->update('{$model->getName()}', array('v_end' => $now), $model->getPrimaryKey());
            /**
             * Insert new version into database
             */
            $model->setVStart($now);
            $this->database->insert('{$model->getName()}', $model->asArray());
        {else}

            $this->database->update('{$model->getName()}', $data, $model->getPrimaryKey());
        {/if}

        }
    }

    public function delete({$model->getModelClass()} $model, $unsaved)
    {
        if ($unsaved) {
            throw new \Exception("Model {$model->getModelClass()} can't be deleted. It is unsaved");
        }
    {if $model->getBehaviour('log')?}

        $now = date('Y-m-d H:i:s', strtotime($this->database->fetchNow()));
        $this->database->update('{$model->getName()}', array('v_end' => $now), $model->getPrimaryKey());
        $model->setVEnd($now);
    {else}

        $this->database->delete('{$model->getName()}', $model->getPrimaryKey());
    {/if}

    }

}