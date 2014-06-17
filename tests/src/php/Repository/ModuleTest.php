<?php

namespace Repository;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    function testModuleRepository()
    {
        $application = getApplication();
        $class = $application->getStorage()->getSchema()->getModel('module')->getRepositoryClass();
        $this->assertSame($class, 'Repository\\Module');

        $master = file_get_contents($application->getProject()->getPath('build php Storage Master.php'));
        $this->assertContains("->get('Repository\\Module')", $master);
    }
}