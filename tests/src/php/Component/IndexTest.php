<?php

namespace Component;

class IndexTest extends \PHPUnit_Framework_TestCase {
    public function testIndex()
    {
        $application = getApplication();
        /**
         * @var $schema \Cti\Storage\Schema
         */
        $schema = $application->getStorage()->getSchema();
        foreach($schema->getModels() as $model) {
            $indexes = $model->getIndexes();
            // only person must have indexes
            if ($model->getName() == 'person') {
                $this->assertCount(1, $indexes);
                $this->assertEquals(array('login'), $indexes[0]->getFields());
            } else {
                $this->assertCount(0, $indexes);
            }
        }
    }

} 