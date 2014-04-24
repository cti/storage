<?php

namespace Behaviour;

use Cti\Storage\Schema;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * migration based test
     */
    function testLinking()
    {
        $application = include __DIR__ . '../../../../resources/php/app.php';

        /**
         * @var Schema $schema
         */
        $schema = $application->getSchema();

        $link = $schema->getModel('person_favorite_module_link');

        $this->assertTrue($link->hasBehaviour('link'));
        $this->assertSame($link->getPk(), array('id_person', 'id_module', 'v_end'));
    }
}