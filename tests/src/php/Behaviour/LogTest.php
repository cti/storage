<?php

namespace Behaviour;

use Cti\Di\Manager;

class LogTest extends \PHPUnit_Framework_TestCase
{
    function testLog()
    {
        $manager = getApplication()->getManager();

        /**
         * @var \Cti\Storage\Component\Model $model
         */
        $model = $manager->create('Cti\Storage\Component\Model', array(
            'name' => 'page'
        ));

        $model->addBehaviour('log');

        $this->assertTrue($model->hasProperty('v_start'));
        $this->assertTrue($model->hasProperty('v_end'));

        $this->assertCount(2, $model->getPk());
        $this->assertSame($model->getPk(), array('id_page', 'v_end'));
    }
}
 