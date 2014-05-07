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

    /**
     * @inject
     * @var \Storage\Master
     */
    protected $master;

    /**
     * @inject
     * @var \Cti\Storage\Adapter\DBAL
     */
    protected $adapter;

    public function init()
    {
        $this->path = dirname(dirname(__DIR__));
    }

    /**
     * @return \Storage\Master
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @return Adapter\DBAL
     */
    public function getAdapter()
    {
        return $this->adapter;
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

    public function registerCommands(Console $console, Manager $manager)
    {
        foreach($this->getClasses('Command') as $class) {
            $console->add($manager->get($class));
        }
    }
}