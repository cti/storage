<?php

namespace Storage\Component;

use Util\String;

use Storage\Behaviour\Factory as BehaviourFactory;

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

    public $one = array();
    public $many = array();

    function __construct($name, $comment, $properties = array())
    {
        $this->name = $name;
        $this->comment = $comment;

        $this->initNames();

        if(count($properties)) {
            $this->initProperties($properties);
        }
    }

    function initNames()
    {
        $this->name_many = String::pluralize($this->name);
        $this->class_name = String::convertToCamelCase($this->name);
        $this->class_name_many = String::pluralize($this->class_name);

        // @todo filesystem based
        $this->repository_class = 'Storage\Repository\\' . $this->class_name.'Repository';
        $this->model_class = 'Storage\Model\\' . $this->class_name.'Base';
    }

    function initProperties($properties)
    {
        foreach ($properties as $key => $config) {
            $this->addProperty($key, $config);
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
        $this->indexes[] = new Index($this, func_get_args());
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

    function createBehaviour($nick, $configuration = array())
    {
        $configuration['model'] = $this;
        return $this->behaviours[$nick] = BehaviourFactory::createBehaviour($nick, $configuration);
    }

    function hasOne(Model $parent, $alias = null)
    {
        if(!$alias) {
            $alias = $parent->name;
        }
        if(!isset($this->one[$alias])) {
            $this->one[$alias] = new Property(array(
                'name' => $alias,
                'type' => 'virtual',
                'comment' => $parent->name,
                'model' => $parent,
            ));
            $parent->hasMany($this, $alias);
        }
    }

    function hasMany(Model $child, $alias = null) 
    {
        if(!$alias) {
            $alias = $child->name;
        }
        if(!isset($this->many[$alias])) {
            $this->many[$alias] = $child;
            $child->hasOne($this, $alias);
        }
    }

    function getRepositoryClass()
    {
        return $this->repository_class;
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
        return !count($this->pk) && !$this->findBehaviourForMethod('getPk');
    }

    protected function getPk()
    {
        if($this->hasVirtualPk()) {
            return array('id_' . $this->name);
        }
        return $this->pk;
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

    protected function getProperties()
    {
        $properties = array_values($this->properties);

        if($this->hasVirtualPk()) {
            $properties['id_' . $this->name] = $this->getProperty('id_'.$this->name);

        }

        foreach($this->behaviours as $behaviour) {
            if(method_exists($behaviour, 'getAdditionalProperties')) {
                foreach($behaviour->getAdditionalProperties() as $property) {
                    $properties[] = $property;
                }
            }
        }

        foreach($this->one as $alias => $property) {
            $properties[] = $property;
        }

        $pk = $other = array();
        foreach($properties as $property) {
            if($property->primary) {
                $pk[$property->name] = $property;
            } elseif($property->type == 'virtual') {
                $last[$property->name] = $property;
            } else {
                $other[$property->name] = $property;
            }
        }

        ksort($pk);
        ksort($other);
        ksort($last);

        $properties = $pk;
        foreach($other as $property) {
            $properties[] = $property;
        }
        foreach($last as $property) {
            $properties[] = $property;
        }

        return $properties;
    }
}