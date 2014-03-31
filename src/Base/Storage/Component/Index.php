<?php

namespace Base\Storage\Component;

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