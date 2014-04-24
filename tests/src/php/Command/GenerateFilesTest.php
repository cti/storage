<?php

namespace Command;

use Storage\Master;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class GenerateFilesTest extends \PHPUnit_Framework_TestCase
{
    function testMaster()
    {
        $application = include __DIR__ . '/../../../resources/php/app.php';
        $generator = $application->getConsole()->find('generate:files');


        $input = new ArrayInput(array(
            'command' => 'generate:files',
        ));

        $output = new NullOutput;
        $generator->run($input, $output);

        $master =  new Master;
        $this->assertNotNull($master);

        $this->assertTrue(method_exists($master, 'getModules'));
        $this->assertTrue(method_exists($master, 'getPersons'));

    }
}
 