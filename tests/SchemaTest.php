<?php

use Cti\Core\Application;

class SchemaTest extends PHPUnit_Framework_TestCase
{
    function getApplication()
    {
        $application = Application::create(implode(DIRECTORY_SEPARATOR, array(__DIR__, 'resources', 'php', 'config.php')));
        $application->register('schema', 'Cti\Storage\Schema');
        return $application;
    }

    function testCreation()
    {
        $schema = $this->getApplication()->getSchema();

        $person = $schema->createModel('person', 'Пользователь', array(
            'login' => 'Имя пользователя',
            'salt' => 'Соль для вычисления хэша',
            'hash' => 'Полученный хэш',
        ));

        $this->assertInstanceOf('Cti\Storage\Component\Model', $person);
        $this->assertSame($person->name, 'person');
        $this->assertSame($person->comment, 'Пользователь');
        
        $this->assertSame($person->getPk(), array('id_person'));
        $this->assertCount(4, $person->getProperties());
    }

    function testException()
    {
        $person = $this->getApplication()->getSchema()->createModel('person', 'person');
        $this->setExpectedException('Exception');
        $person->callUnknownMethod();
    }
}