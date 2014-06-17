<?php

namespace Cti\Storage\Generator;

use Cti\Core\String;

class Coffee
{
    public $model;

    public function getModelCode()
    {
        $class = $this->model->getClassName();

        return <<<COFFEE
# Create file Model/$class.coffee in you project coffee source for override
Ext.define 'Model.$class',
  extend: 'Model.Generated.$class'
COFFEE;
    }

    public function getGeneratedCode()
    {
        $class = $this->model->getClassName();

        $name = json_encode($this->model->getName());

        $pk = $this->model->getPk();
        $idProperty = json_encode(count($pk) == 1 ? $pk[0] : $pk);

        $fields = array();
        foreach($this->model->getProperties() as $property) {
            $fields[] = array('name' => $property->getName(), 'type' => $property->getJavascriptType());
        }
        $fields = json_encode($fields);

        return <<<COFFEE
Ext.define 'Model.Generated.$class',

  extend: 'Ext.data.Model'
  name: $name
  idProperty: $idProperty
  fields: $fields
COFFEE;

    }
}