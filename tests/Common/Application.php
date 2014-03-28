<?php

namespace Common;

use Di\Manager;

class Application
{
    /**
     * @inject
     * @var Common\Module
     */
    protected $module;

    public function getModule()
    {
        return $this->module;
    }

    public function extractModuleFromManager(Manager $manager) 
    {
        return $manager->get('Common\Module');
    }
}