<?php

namespace Nekufa\Storage\Component;

class Index
{
    protected $fields;
    
    function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function getFields() 
    {
        return $this->fields;
    }
}