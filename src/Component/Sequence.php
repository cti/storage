<?php

namespace Cti\Storage\Component;

use Cti\Storage\Schema;
use Cti\Storage\Component\Model;

class Sequence
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var String
     */
    protected $name;

    public function __construct($name, Model $model)
    {
        $this->model = $model;
        $this->name = $name;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getName()
    {
        return $this->name;
    }
}