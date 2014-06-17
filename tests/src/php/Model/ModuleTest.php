<?php

namespace Model;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    function testModuleModel()
    {
        $application = getApplication();
        $class = $application->getStorage()->getSchema()->getModel('module')->getModelClass();
        $this->assertSame($class, 'Model\\Module');

        $repository = file_get_contents($application->getProject()->getPath('build php Storage Repository ModuleRepository.php'));
        $this->assertContains('use Model\\Module as Module', $repository);
    }
}