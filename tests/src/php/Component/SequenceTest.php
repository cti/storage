<?php
namespace Component;

use Cti\Storage\Schema;

class SequenceTest extends \PHPUnit_Framework_TestCase
{
    public function testSequences()
    {
        $application = getApplication();
        /**
         * @var $schema Schema
         */
        $schema = $application->getStorage()->getSchema();
        $sequences = $schema->getSequences();
        $this->assertCount(2, $sequences);
        $this->assertArrayHasKey('sq_person', $sequences);

        $person_model = $schema->getModel('person');
        $person_sequence = $sequences['sq_person'];
        $this->assertEquals($person_sequence, $person_model->getSequence());
    }

} 