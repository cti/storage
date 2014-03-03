<?php

namespace Storage\Component;

class Index
{
    function __construct(Model $model, $fields)
    {
        $this->model = $model;
        $this->fields = $fields;
    }
}