<?php
namespace Component;

class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testRelation()
    {
        /**
         * @var $schema \Cti\Storage\Schema
         */
        $schema = getApplication()->getSchema();
        $person = $schema->getModel('person');

        $personInReferences = $person->getInReferences();
        $this->assertCount(3, $personInReferences);
        $map = array();
        foreach($personInReferences as $reference) {
            $map[] = $reference->getSource().':'.$reference->getDestination();
        }
        $this->assertContains("module:person", $map);
        $this->assertContains("person_favorite_module_link:person", $map);
        $this->assertContains("module_developer_link:person", $map);

        $personOutReferences = $person->getOutReferences();
        $this->assertCount(1, $personOutReferences);
        $this->assertEquals("person", $personOutReferences[0]->getSource());
        $this->assertEquals("module", $personOutReferences[0]->getDestination());
        $this->assertEquals("default_module", $personOutReferences[0]->getDestinationAlias());
    }

} 