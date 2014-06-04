<?php
namespace Cti\Storage\Converter;

use Cti\Storage\Component\Model;
use Cti\Storage\Component\Property;
use Cti\Storage\Component\Reference;
use Cti\Storage\Schema;

class SchemaToArray
{
    public function convert(Schema $schema)
    {
        $result = array(
            'models' => array(),
        );
        foreach($schema->getModels() as $model) {
            $result['models'][$model->getName()] = $this->modelAsArray($model);
        }
        return $result;
    }

    protected function modelAsArray(Model $model) {
        $result = array(
            'pk' => $model->getPk(),
            'has_log' => boolval($model->getBehaviour('log')),
            'is_link' => boolval($model->getBehaviour('link')),
            'properties' => array(),
            'references' => array(),
        );
        foreach($model->getProperties() as $property) {
            $result['properties'][$property->getName()] = $this->propertyAsArray($property);
        }
        foreach($model->getOutReferences() as $reference) {
            $result['references'][] = $this->referenceAsArray($reference);
        }
        return $result;
    }

    protected function propertyAsArray(Property $property)
    {
        return array(
            'comment' => $property->getComment(),
            'type' => $property->getType(),
            'required' => $property->getRequired(),
        );
    }

    protected function referenceAsArray(Reference $reference)
    {
        $result = array(
            'destination' => $reference->getDestination(),
            'destination_alias' => $reference->getDestinationAlias(),
            'properties' => array(),
        );
        foreach($reference->getProperties() as $property) {
            $result['properties'][] = $property->getName();
        }
        return $result;
    }
} 