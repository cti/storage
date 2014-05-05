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

        if ($dbal->getDriver()->getName() == 'pdo_sqlite') {
            $query = "select current_timestamp";

        } elseif ($dbal->getDriver()->getName() == 'oci8') {
            $query = "select sysdate from dual";

        } elseif ($dbal->getDriver()->getName() == 'pdo_pgsql') {
            $query = "select 'now'::timestamp";
        } else {
            throw new \Exception("Undefined driver {$dbal->getDriver()->getName()}");
        }
        $dbnow = $dbal->fetchColumn($query);
        $this->assertNotEmpty($dbnow);
    }

} 