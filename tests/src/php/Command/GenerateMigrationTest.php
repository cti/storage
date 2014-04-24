<?php

namespace Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GenerateMigrationTest extends \PHPUnit_Framework_TestCase
{
    function testGenerator()
    {
        $application = getApplication();
        $migration = $application->getConsole()->find('generate:migration');

        $input = new ArrayInput(array(
            'command' => 'generate:migration',
            'name' => array('hello', 'world'),
        ));

        $output = new NullOutput;
        $migration->run($input, $output);

        $finder = new Finder;
        $finder
            ->files()
            ->name("*.php")
            ->in($application->getPath('resources php migrations'));


        $found = false;

        foreach ($finder as $file) {
            if (strpos($file->getBasename(), 'hello_world')) {
                $filesystem = new Filesystem;
                $filesystem->remove($file->getRealPath());
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }
}
 