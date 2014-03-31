<?php

namespace Base\Storage\Component;

use Base\Storage\Behaviour\Factory as BehaviourFactory;
use Base\Util\String;
use Exception;

class Model
{
    public $name;
    public $class_name;
    public $repository_class;
    public $comment;

    public $pk = array();
    public $indexes = array();
    public $properties = array();
    public $behaviours = array();

    public $relations = array();
    public $references = array();

    public $links = array();

    function init()
    {
        $this->name_many = String::pluralize($this->name);
        $this->class_name = String::convertToCamelCase($this->name);
        $this->class_name_many = String::pluralize($this->class_name);
        $this->repository_class = 'Storage\Repository\\' . $this->class_name . 'Repository';
        $this->model_class = 'Storage\Model\\' . $this->class_name . 'Base';
        
        if(count($this->properties)) {
            $properties = $this->properties;
            $this->properties = array();
            foreach ($properties as $key => $config) {
                $this->addProperty($key, $config);
            }
        }
    }

    public function addProperty($name, $config)
    {
        if (is_string($config)) {
            $config = array('name' => $name, 'comment' => $config);
        } elseif (is_array($config) && !is_numeric($name)) {
            if(isset($config[0])) {
                array_unshift($config, $name);
            } else {
                $config['name'] = $name;
            }
        }
        $property = new Property($config);
        $this->properties[$property->name] = $property;
    }


    function createIndex($field)
    {
        $this->indexes[] = new Index(func_get_args());
    }

    function removeIndex(Index $index)
    {
        foreach($this->indexes as $k => $v) {
            if($v == $index) {
                unset($this->indexes[$k]);
                $this->indexes = array_values($this->indexes);
                break;
            }
        }
    }

    function listIndexes()
    {
        return $this->indexes;
    }

    function createBehaviour($nick, $configuration = array())
    {
        return $this->behaviours[$nick] = BehaviourFactory::createInstance($nick, $configuration);;
    }

    function hasBehaviour($nick) 
    {
        return isset($this->behaviours[$nick]);
    }

    function getBehaviour($nick) 
    {
        return isset($this->behaviours[$nick]) ? $this->behaviours[$nick] : null;
    }

    function hasOne($parent)
    {
        $parent_name = $parent instanceof Model ? $parent->name : $parent;
        $relation = new Relation($this->name, $parent_name);
        $this->relations[] = $relation;
        return $relation;
    }

    function registerLink(Model $parent, $alias) 
    {
        if(isset($this->links[$alias])) {
            throw new Exception(sprintf(
                "Duplicate link %s on %s throw %s and %s", 
                $alias,
                $this->name,
                $parent->name,
                $this->links[$alias]->name
            ));
            
        }
        $this->links[$alias] = $parent;
    }

    public function __call($name, $params)
    {
        $behaviour = $this->findBehaviourForMethod($name);
        $instance = $behaviour ? $behaviour : $this;
        return call_user_func_array(array($instance, $name), $params);
    }

    protected function findBehaviourForMethod($name) 
    {
        $found = array();
        foreach($this->behaviours as $behaviour) {
            if(method_exists($behaviour, $name)) {
                $found[] = $behaviour;
            }
        }
        if(count($found) > 1) {
            throw new Exception("Behaviour conflict for ". implode(' and ', $found));
        }
        return count($found) ? $found[0] : null;
    }

    protected function hasVirtualPk()
    {
        return !count($this->pk) && !$this->findBehaviourForMethod('getPk') && !$this->findBehaviourForMethod('hasVirtualPk');
    }

    protected function getPk()
    {
        $pk = $this->getModelPk();
        foreach($this->behaviours as $behaviour) {
            if(method_exists($behaviour, 'getAdditionalPk')) {
                foreach ($behaviour->getAdditionalPk() as $key) {
                    $pk[] = $key;
                }
            }
        }
        return $pk;
    }

    protected function getModelPk()
    {
        return $this->hasVirtualPk() ? array('id_' . $this->name) : $this->pk;
    }

    protected function getProperties()
    {
        $properties = $this->properties;

        if($this->hasVirtualPk()) {
            $properties['id_' . $this->name] = $this->getProperty('id_'.$this->name);
        }

        foreach($this->behaviours as $behaviour) {
            if(method_exists($behaviour, 'getAdditionalProperties')) {
                foreach($behaviour->getAdditionalProperties() as $property) {
                    if(isset($properties[$property->name])) {
                        throw new Exception(sprintf("Duplicate property %s.%s", $this->name, $property->name));
                    }
                    $properties[$property->name] = $property;
                }
            }
        }

        $pk = $other = array();
        foreach($properties as $property) {
            if($property->primary) {
                $pk[$property->name] = $property;
            } else {
                $other[$property->name] = $property;
            }
        }

        ksort($pk);
        ksort($other);

        $properties = $pk;
        foreach($other as $property) {
            $properties[] = $property;
        }   
        return $properties;
    }

    protected function getProperty($name)
    {
        if($name == 'id_' . $this->name && $this->hasVirtualPk()) {
            if(isset($this->pk_field)) {
                return $this->pk_field;
            }
            return $this->pk_field = new Property(array(
                'name' => 'id_' . $this->name, 
                'comment' => 'id_' . $this->name,
                'primary' => true,
            ));
        }

        if(!isset($this->properties[$name])) {
            foreach ($this->behaviours as $behaviour) {
                if(method_exists($behaviour, 'getAdditionalProperty')) {
                    $property = $behaviour->getAdditionalProperty($name);
                    if($property) {
                        return $property;
                    }
                }
            }
        }

        return $this->properties[$name];
    }

    public function hasOwnQuery()
    {
        if(count($this->indexes)) {
            return true;
        }

        // todo check user defined query
    }

    public function getQueryClass() 
    {
        if($this->hasOwnQuery()) {
            // todo check user defined query
            return 'Storage\Query\\' . $this->class_name .'Select';
        }
        return 'Storage\Query\Select';
    }
}