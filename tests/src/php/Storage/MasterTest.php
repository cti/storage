<?php

namespace Storage;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class MasterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Storage\Master
     */
    protected $master;

    public function setUp()
    {
        $this->master = getApplication()->getStorage()->getMaster();
    }

    public function testMaster()
    {
        $personRepository = $this->master->getPersons();
        $this->assertNotNull($personRepository);
        $this->assertEquals("Storage\\Repository\\PersonRepository", get_class($personRepository));
    }

    /**
     * To use models and repos we need to have database
     */
    protected function syncDatabase()
    {
        $generator = getApplication()->getConsole()->find("generate:database");

        $input = new StringInput("generate:database");
        $output = new NullOutput();
        ob_start();
        $generator->run($input, $output);
        ob_end_clean();
    }
}