<?php

namespace Command;

class GenerateDatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testSchemaConverter()
    {
        $this->markTestSkipped();

        $application = include __DIR__ . '/../../../resources/php/app.php';
        /**
         * @var $schema \Cti\Storage\Schema
         */
        $schema = $application->getSchema();
        /**
         * @var $converter \Cti\Storage\Converter\DBAL
         */
        $converter = $application->getManager()->get('\Cti\Storage\Converter\DBAL');
        $dbalSchema = $converter->convert($schema);
        foreach($schema->getModels() as $model) {
            $table = $dbalSchema->getTable($model->getName());
            // check table existence
            $this->assertNotEmpty($table);
            $properties = $model->getProperties();
            $this->assertEquals(count($properties), count($table->getColumns()));
            foreach($properties as $property) {
                $column = $table->getColumn($property->getName());
                $this->assertNotEmpty($column); // check column existance
                $this->assertEquals($property->getType(), $column->getType());
                $this->assertEquals($property->getRequired(), $column->getNotnull());

            }
        }
    }
}