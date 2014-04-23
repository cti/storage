<?php

namespace Cti\Storage\Converter;
/**
 * Class DBAL
 * Converts Cti\Storage\Schema to Doctrine Schema
 * @package Cti\Storage\Converter
 */
class DBAL {

    /**
     * @inject
     * @var \Cti\Core\Application
     */
    protected $application;
    /**
     * @var \Doctrine\DBAL\Schema\Schema
     */
    protected $schema;
    /**
     * @var \Cti\Storage\Schema
     */
    protected $inputSchema;

    public function convert(\Cti\Storage\Schema $inputSchema)
    {
        $schema = new \Doctrine\DBAL\Schema\Schema;


        foreach($inputSchema->getModels() as $model) {
//            $table = $schema->createTable($model->name);
//            foreach($model->getProperties() as $property) {
                /**
                 * @var $property \Cti\Storage\Component\Property
                 */
//                $table->addColumn($property->name, $property->type, array(
//                    'comment' => $property->comment,
//                ));
//            }
        }
        return $schema;
    }
}