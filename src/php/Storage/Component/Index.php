<?php

namespace Storage\Component;

class Index
{
    protected $fields;
    
    function __construct($fields)
    {
        $this->fields = $fields;
    }
}