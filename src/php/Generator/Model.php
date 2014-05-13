<?php

namespace Cti\Storage\Generator;

class Model
{
    /**
     * @inject
     * @var \Cti\Core\Module\Fenom
     */
    protected $fenom;

    /**
     * @var \Cti\Storage\Component\Model
     */
    protected $model;

    /**
     * @inject
     * @var \Cti\Storage\Schema
     */
    protected $schema;

    public function getCode()
    {
        $code = $this->fenom->render('model', array(
            'model' => $this->model,
            'schema' => $this->schema
        ));
        return $code;
    }
}