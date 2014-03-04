<?php

namespace Storage\Behaviour;

use Exception;
use Symfony\Component\Finder\Finder;
use Util\String;

abstract class Factory 
{
    public static function getBehaviourClass($nick)
    {
        static $mapping;

        if(is_null($mapping)) {

            $mapping = array();
            $finder = new Finder();

            $finder
                ->files()
                ->notName('Factory.php')
                ->name("*.php")
                ->in(__DIR__);

            foreach($finder as $file) {

                $name = $file->getBasename('.php');
                $alias = String::camelCaseToUnderScore($name);
                $classname = 'Storage\\Behaviour\\' . $name;
                $mapping[$alias] = $classname;
            }
        }

        if(!isset($mapping[$nick])) {
            throw new Exception("Alias $nick was not registered");
        }

        return $mapping[$nick];
    }
}