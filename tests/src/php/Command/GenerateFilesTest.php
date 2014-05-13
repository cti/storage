<?php

namespace Command;

use Storage\Master;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class GenerateFilesTest extends \PHPUnit_Framework_TestCase
{
    function testMaster()
    {
        $application = getApplication();
        /**
         * @var $generator \Cti\Storage\Command\GenerateFiles
         */
        $generator = $application->getConsole()->find('generate:files');


        $input = new ArrayInput(array(
            'command' => 'generate:files',
        ));

        $output = new NullOutput;
        $generator->run($input, $output);
    }
}
 