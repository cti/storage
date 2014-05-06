<?php
namespace Component;

class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testRelation()
    {
        /**
         * @var $schema \Cti\Storage\Schema
         */
        $schema = getApplication()->getStorage()->getSchema();
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

        $personOutReferences[0]->process($schema);
        $outReference = $personOutReferences[0];
        $this->assertEquals("person", $outReference->getSource());
        $this->assertEquals("module", $outReference->getDestination());
        $this->assertEquals("default_module", $outReference->getDestinationAlias());
        $this->assertEquals('merge', $outReference->getStrategy());

        $outReferenceProperties = $outReference->getProperties();
        $this->assertCount(1, $outReferenceProperties);

        $personModuleLink = $outReferenceProperties['id_module_default_module'];
        $this->assertEquals('id_module', $personModuleLink->getForeignName());

        $pfml = $schema->getModel("person_favorite_module_link");
        foreach($pfml->getOutReferences() as $reference) {
            // @todo Expand reference test
        }
    }

} 