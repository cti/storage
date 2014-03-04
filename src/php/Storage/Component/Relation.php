<?php

namespace Storage\Component;

use Storage\Schema;

class Relation
{
    public $source;
    public $destination;

    public $destination_alias;
    public $referenced_by;

    function __construct($source, $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
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
    
    function getSourceAlias()
    {
        return $this->source_alias ? $source_alias : $this->source;
    }

    function getReferenced()
    {
        return $this->referenced_by ? $this->referenced_by : $this->destination;
    }

    function getMapping(Schema $schema)
    {
        $schema->getModel($this->source);
        $schema->getModel($this->destination);
    }
}