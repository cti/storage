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
        $fs = new Filesystem;
        $fs->remove(__DIR__ . DIRECTORY_SEPARATOR . 'build');
        $application = Application::create(implode(DIRECTORY_SEPARATOR, array(__DIR__, 'resources', 'php', 'config.php')));
        $application->extend('Cti\Storage\Extension');
        return $application;
    }

    function testGenerator()
    {
        $application = $this->getApplication();

        $generator = $application->getConsole()->find('generate:files');

        $input = new ArrayInput(array(
            'command' => 'generate:files', 
        ));

        $output = new NullOutput;
        $generator->run($input, $output);

        $master =  new \Storage\Master;
        $this->assertNotNull($master);
    }
}
