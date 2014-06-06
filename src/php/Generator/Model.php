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
        $code = $this->fenom->render('php model', array(
            'model' => $this->model,
            'schema' => $this->schema,
            'generator' => $this,
        ));
        return $code;
    }

    /**
     * @param \Cti\Storage\Component\Model $linkModel
     */
    public function getOppositeModel($linkModel)
    {
        foreach($linkModel->getReferences() as $reference) {
            if ($reference->getDestination() != $this->model->getName()) {
                return $this->schema->getModel($reference->getDestination());
            }
        }
    }


}