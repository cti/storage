<?php

use Cti\Core\Application;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class SchemaTest extends PHPUnit_Framework_TestCase
{
    function testMigration()
    {
        $application = $this->getApplication();

        $migration = $application->getConsole()->find('generate:migration');

        $input = new ArrayInput(array(
            'command' => 'generate:migration', 
            'name' => array('hello','world'),
        ));

        $output = new NullOutput;
        $migration->run($input, $output);

        $finder = new Finder;
        $finder
            ->files()
            ->name("*.php")
            ->in($application->getPath('resources php migrations'));


        $found = false;

        foreach($finder as $file) {
            if(strpos($file->getBasename(), 'hello_world')) {
                $filesystem = new Filesystem;
                $filesystem->remove($file->getRealPath());
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }

    function getApplication()
    {
        $application = Application::create(implode(DIRECTORY_SEPARATOR, array(__DIR__, 'resources', 'php', 'config.php')));
        $application->extend('Cti\Storage\Extension');
        return $application;
    }

    function testBasics()
    {
        $schema = $this->getApplication()->getSchema();

        // $person = $schema->createModel('person', 'Пользователь', array(
        //     'login' => 'Имя пользователя',
        //     'salt' => 'Соль для вычисления хэша',
        //     'hash' => 'Полученный хэш',
        // ));

        // // find by login
        // $person->createIndex('login');

        // $this->assertInstanceOf('Cti\Storage\Component\Model', $person);
        // $this->assertSame($person->name, 'person');
        // $this->assertSame($person->comment, 'Пользователь');

        // $this->assertSame($person->getPk(), array('id_person'));
        // $this->assertCount(4, $person->getProperties());
    }

    function testException()
    {
        // $person = $this->getApplication()->getSchema()->createModel('person', 'person');
        // $this->setExpectedException('Exception');
        // $person->callUnknownMethod();
    }
}