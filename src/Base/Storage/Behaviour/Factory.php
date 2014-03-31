<?php

namespace Base\Storage\Behaviour;

use Base\Util\String;
use Exception;
use Symfony\Component\Finder\Finder;

abstract class Factory 
{

    public static function createInstance($nick, $options = array())
    {
        $class = self::getBehaviourClass($nick);
        $instance = new $class;
        foreach($options as $k => $v) {
            $instance->$k = $v;
        }
        return $instance;
    }

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