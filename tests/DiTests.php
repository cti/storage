<?php

use Di\Configuration;
use Di\Manager;

class DiTests extends PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $m = new Manager;
        $this->assertInstanceOf('Common\Module', $m->get('Common\Module'));
        $this->assertNotSame($m->get('Common\Module'), $m->create('Common\Module'));
    }

    public function testInstanceConfiguration()
    {
        $configuration = new Configuration(array(
            'Common\Module' => array(
                'state' => 'active'
            )
        ));

        $m = new Manager($configuration);

        $this->assertSame($m->get('Common\Module')->getState(), 'active');
        $this->assertSame($m->create('Common\Module')->getState(), 'active');

        $instance = $m->create('Common\Module', array(
            'state' => 'unknown'
        ));

        $this->assertSame($instance->getState(), 'unknown');
    }

    public function testPropertyInjection() 
    {
        $m = new Manager;
        $module = $m->get('Common\Application')->getModule();
        $this->assertSame($module, $m->get('Common\Module'));
    }

    public function testMethodInjection()
    {
        $m = new Manager;

        $this->assertSame(
            $m->call('Common\Application', 'extractModuleFromManager'), 
            $m->get('Common\Module')
        );
    }
}
