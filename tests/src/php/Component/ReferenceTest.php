<?php
namespace Component;

class ReferenceTest
{
    public function testRelation()
    {
        /**
         * @var $schema \Cti\Storage\Schema
         */
        $schema = getApplication()->getSchema();
        $person = $schema->getModel('person');

    }

} 