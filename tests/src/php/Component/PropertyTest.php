<?php

namespace Component;

use Cti\Storage\Schema;

class PropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testProperties()
    {
        $application = getApplication();
        /**
         * @var $schema Schema
         */
        $schema = $application->getStorage()->getSchema();
        $model = $schema->getModel('person');

        // comment, required and string type check
        $login = $model->getProperty('login');
        $this->assertNotEmpty($login);
        $this->assertEquals('Имя пользователя', $login->getComment());
        $this->assertEquals('string', $login->getType());
        $this->assertTrue($login->getRequired());

        // type check
        $model = $schema->getModel('person_favorite_module_link');
        $rating = $model->getProperty('rating');
        $this->assertEquals('integer', $rating->getType());
        $this->assertEquals(100, $rating->getMax());
        $this->assertEquals(0, $rating->getMin());
        $this->assertFalse($rating->getPrimary());

        $model->addProperty("test", array(
            "comment" => 'test',
            "type" => 'integer'
        ));

        // test property creation and removing
        $property = $model->getProperty("test");
        $this->assertNotEmpty($property);
        $this->assertEquals("test", $property->getComment());
        $this->assertEquals("integer", $property->getType());
        $this->assertEquals($model, $property->getModel());

        $model->removeProperty("test");
        $this->setExpectedException("Exception");
        $model->getProperty("test");
    }

}