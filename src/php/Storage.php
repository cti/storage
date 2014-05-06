<?php
namespace Cti\Storage;

use Build\Application;
use Cti\Core\Application\Bootstrap;
use Cti\Core\Module\Console;
use Cti\Core\Module\Project;
use Cti\Di\Manager;
use Cti\Di\Reflection;

class Storage extends Project implements Bootstrap
{
    /**
     * @inject
     * @var \Cti\Storage\Schema
     */
    protected $schema;

    public function init()
    {
        $this->path = dirname(dirname(__DIR__));
    }


    public function getMaster()
    {

    }

    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * bootstrap application
     * @param Application $application
     * @return mixed
     */
    public function boot(Application $application)
    {
        $initializer = $application->getManager()->getInitializer();
        $initializer->before('Cti\Core\Module\Console', array($this, 'registerCommands'));
    }

    public function registerCommands(Console $console, Manager $manager, Application $application)
    {
        foreach($this->getClasses('Command') as $class) {
            $console->add($manager->get($class));
        }
    }
}