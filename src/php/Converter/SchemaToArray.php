<?php
namespace Cti\Storage\Converter;

class SchemaToArray
{
    public function convert(\Cti\Storage\Schema $schema)
    {
        foreach($schema->getModels() as $model) {
            print_r($model);
        }
    }
} 