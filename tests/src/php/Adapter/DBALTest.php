<?php

namespace Adapter;

class DBALTest extends \PHPUnit_Framework_TestCase
{
    public function testDBALAdapter()
    {
        $application = getApplication();

        /**
         * @var $manager \Cti\Di\Manager
         */
        $manager = $application->getManager();

        /**
         * constructor and connect check
         * @var $dbal \Cti\Storage\Adapter\DBAL
         */
        $dbal = $manager->get('Cti\Storage\Adapter\DBAL');

        $dbnow = $dbal->fetchColumn("select current_timestamp");
        $this->assertNotEmpty($dbnow);
    }

} 