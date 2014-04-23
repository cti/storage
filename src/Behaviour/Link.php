<?php

namespace Cti\Storage\Behaviour;

use Cti\Storage\Component\Model;

class Link extends Behaviour
{

    /**
     * @var Model[]
     */
    protected $list = array();

    function init(Model $model)
    {
        $model->removeBehaviour('id');
    }


    /**
     * @param Model $model
     * @return Model
     */
    function getForeignModel(Model $model)
    {
        foreach($this->list as $variant) {
            if($variant != $model) {
                return $variant;
            }
        }
    }

    function getPk()
    {
        $result = array();
        foreach($this->list as $model) {
            foreach($model->getPk() as $field) {
                if(!in_array($field, $result)) {
                    $result[] = $field;
                }
            }
        }
        return $result;
    }
}