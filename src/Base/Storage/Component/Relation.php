<?php

namespace Base\Storage\Component;

use Base\Storage\Schema;

class Relation
{
    public $source;
    public $destination;

    public $destination_alias;
    public $referenced_by;

    public $strategy = 'merge';
    public $properties = array();

    function __construct($source, $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
        
        $this->referenced_by = $source;
        $this->destination_alias = $destination;
    }

    function usingAlias($name)
    {
        $this->destination_alias = $name;
        return $this;
    }

    function referencedBy($name)
    {
        $this->referenced_by = $name;
        return $this;
    }

    function setStrategy($strategy) 
    {
        $this->strategy = $strategy;
        return $this;
    }
    
    function process(Schema $schema) 
    {
        $source = $schema->getModel($this->source);
        $destination = $schema->getModel($this->destination);

        foreach($destination->getPk() as $key) {
            $property = $destination->getProperty($key);
            if(!$property->behaviour) {
                $name = $this->destination_alias != $this->destination ? $key . '_' . $this->destination_alias : $key;
                $this->properties[$name] = $source->properties[$name] = new Property(array(
                    'name' => $name,
                    'foreignName' => $key,
                    'comment' => $this->destination_alias.' link',
                    'type' => $property->type,
                    'relation' => $this,
                    'readonly' => true,
                ));

                if(!$source->hasVirtualPk()) {
                    $source->pk[] = $name;
                }
            }
        }

        $destination->references[] = $this;
    }
}