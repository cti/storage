<?php

class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testSchema()
    {
        $application = getApplication();

        /**
         * @var $schema \Cti\Storage\Schema
         */
        $schema = $application->getSchema();
        $this->assertNotEmpty($schema->getModel('person'));
        $schema->createModel('test', "Тестовая модель", array(
            'text' => 'Текст'
        ));
        $this->assertNotEmpty($schema->getModel('test'));
        $schema->removeModel('test');
        $this->setExpectedException('Exception', 'Model test not found in schema');
        $schema->removeModel('test');


    }
} 