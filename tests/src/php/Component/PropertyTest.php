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
        $schema = $application->getSchema();
        $model = $schema->getModel('person');

        // comment, required and string type check
        $login = $model->getProperty('login');
        $this->assertNotEmpty($login);
        $this->assertEquals('Имя пользователя', $login->getComment());
        $this->assertEquals('string', $login->getType());
        $this->assertTrue($login->getRequired());

//        $default_module = $model->getProperty('default_module');

        // type check
        $model = $schema->getModel('person_favorite_module_link');
        $rating = $model->getProperty('rating');
        $this->assertEquals('integer', $rating->getType());
        $this->assertEquals(100, $rating->getMax());
        $this->assertEquals(0, $rating->getMin());
        $this->assertFalse($rating->getPrimary());
    }

}