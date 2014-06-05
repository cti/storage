<?php

namespace Cti\Storage;

use Build\Application;
use Cti\Core\Application\Bootloader;
use Cti\Core\Module\Cache;
use Cti\Core\Module\Console;
use Cti\Core\Module\Fenom;
use Cti\Core\Module\Project;

class Module extends Project implements Bootloader
{

    /**
     * @inject
     * @var Application
     */
    public $application;

    public function init(Cache $cache)
    {
        parent::init($cache);
        $this->path = dirname(dirname(__DIR__));
        $this->prefix = 'Cti\\Storage\\';
    }

    public function boot(Application $application)
    {
        $init = $application->getManager()->getInitializer();
        $init->after('Cti\Core\Module\Console', array($this, 'registerCommands'));
        $init->after('Cti\Core\Module\Fenom', array($this, 'addSource'));
    }

    public function registerCommands(Console $console, Application $application)
    {
        foreach($this->getClasses('Command') as $class) {
            $console->add($this->application->getManager()->get($class));
        }
    }

    public function addSource(Fenom $fenom)
    {
        $fenom->addSource($this->getPath('resources fenom'));

    }

    public function getSchema()
    {
        return $this->application->getManager()->get("Cti\\Storage\\Schema");
    }

    protected function getAvailableNamespaces()
    {
        return array('Command');
    }
}