<?php

namespace Converter;

class DBALTest extends \PHPUnit_Framework_TestCase
{
    public function testDBAL()
    {
        $application = getApplication();
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
                $this->assertEquals($property->getType(), $column->getType()->getName());
                $this->assertEquals($property->getRequired(), $column->getNotnull());

            }
            $this->assertCount(count($model->getProperties()), $table->getColumns());

            // primary key equals
            if (count($model->getPk())) {
                $this->assertNotEmpty($table->getPrimaryKey(), "Table \"{$table->getName()}\" don't have any primary key");
                $this->assertEquals($table->getPrimaryKey()->getColumns(), $model->getPk(), "Table \"{$table->getName()}\" have invalid primary key");
            } else {
                $this->assertEmpty($table->getPrimaryKey());
            }

            //indexes isset
            $tableIndexes = array();
            foreach($table->getIndexes() as $index) {
                $tableIndexes[] = implode(':', $index->getColumns());
            }
            foreach($model->getIndexes() as $index) {
                $this->assertContains(implode(':',$index->getFields()), $tableIndexes, "Table don't have index with fields \"" . implode(':',$index->getFields()) . "\"");
            }

            // check foreign keys
            $tableFKs = array();
            foreach($table->getForeignKeys() as $key) {
                $tableFKs[] = $key->getForeignTableName() . "-" . implode(':',$key->getColumns()) . "-" . implode(':', $key->getForeignColumns());
            }
            foreach($model->getReferences() as $relation) {

            }
        }
    }
} 