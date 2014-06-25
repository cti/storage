<?php

namespace Cti\Storage\Component;

use Cti\Storage\Behaviour\Behaviour;
use Cti\Core\String;
use Exception;

class Model
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $class_name;

    /**
     * @var string
     */
    protected $class_name_many;

    /**
     * @var string
     */
    protected $repository_class;

    /**
     * @var string
     */
    protected $model_class;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var array
     */
    protected $pk = array();

    /**
     * @var Index[]
     */
    protected $indexes = array();

    /**
     * @var Property[]
     */
    protected $properties = array();

    /**
     * @var Behaviour[]
     */
    protected $behaviours = array();

    /**
     * @var array
     */
    protected $references = array(
        'in' => array(),
        'out' => array(),
    );

    /**
     * @var Model[]
     */
    protected $links = array();

    /**
     * @inject
     * @var \Build\Application
     */
    protected $application;

    /**
     * @var \Cti\Storage\Component\Sequence
     */
    protected $sequence;

    function init()
    {
        $this->name_many = String::pluralize($this->name);
        $this->class_name = String::convertToCamelCase($this->name);
        $this->class_name_many = String::pluralize($this->class_name);
        $this->repository_class = 'Storage\Repository\\' . $this->class_name . 'Repository';
        $this->model_class = 'Storage\Model\\' . $this->class_name . 'Base';

        $model_file = $this->application->getProject()->getPath('src php Model ' . $this->class_name . '.php');
        if(file_exists($model_file)) {
            $this->model_class = 'Model\\' . $this->class_name;
        }

        $repository_file = $this->application->getProject()->getPath('src php Repository ' . $this->class_name . '.php');
        if(file_exists($repository_file)) {
            $this->repository_class = 'Repository\\' . $this->class_name;
        }

        if(count($this->properties)) {
            $properties = $this->properties;
            $this->properties = array();
            foreach ($properties as $key => $config) {
                if($config instanceof Model) {
                    $reference = $this->hasOne($config);
                    if(is_string($key)) {
                        $reference->usingAlias($key);
                    }
                } else {
                    $this->addProperty($key, $config);
                }
            }
        }

        if(!$this->getPk()) {
            $this->addBehaviour('id');
        }
    }

    /**
     * @param string $field
     * @return Index
     */
    function createIndex($field)
    {
        return $this->indexes[] = new Index(func_get_args());
    }

    /**
     * @param Index $index
     * @return $this
     */
    function removeIndex(Index $index)
    {
        $key = array_search($index, $this->indexes);
        unset($this->indexes[$key]);
        $this->indexes = array_values($this->indexes);
        return $this;
    }

    /**
     * @return Index[]
     */
    function listIndexes()
    {
        return $this->indexes;
    }

    /**
     * @param $nick
     * @param array $configuration
     * @return Behaviour
     */
    function addBehaviour($nick, $configuration = array())
    {
        return $this->behaviours[$nick] = Behaviour::create($this, $nick, $configuration);
    }

    /**
     * @param $nick
     * @return bool
     */
    function hasBehaviour($nick) 
    {
        return isset($this->behaviours[$nick]);
    }

    /**
     * @param $nick
     * @return Behaviour
     */
    function getBehaviour($nick) 
    {
        return isset($this->behaviours[$nick]) ? $this->behaviours[$nick] : null;
    }

    /**
     * @param $nick
     * @throws \Exception
     */
    function removeBehaviour($nick)
    {
        if(!$this->hasBehaviour($nick)) {
            throw new Exception(sprintf('Behaviour %s not found!', $nick));
        }
        unset($this->behaviours[$nick]);
    }

    /**
     * @param mixed $parent
     * @return Reference
     */
    function hasOne($parent)
    {
        $parent_name = $parent instanceof Model ? $parent->name : $parent;
        $reference = new Reference($this->name, $parent_name);
        $this->references['out'][$reference->getDestination()] = $reference;
        return $reference;
    }

    /**
     * @param Model $parent
     * @param string $alias
     * @throws \Exception
     */
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

    public function setPk($array)
    {
        foreach($array as $k) {
            if(!$this->hasProperty($k)) {
                throw new Exception(sprintf('Property %s not found!', $k));
            }
        }
        $this->pk = $array;
    }

    /**
     * @return array
     */
    public function getPk()
    {
        $pk = $this->pk;
        foreach($this->behaviours as $behaviour) {
            foreach($behaviour->getPk() as $field) {
                if(!in_array($field, $pk)) {
                    $pk[] = $field;
                }
            }
        }
        sort($pk);
        return $pk;
    }

    /**
     * @param $name
     * @param $config
     * @return Property
     */
    public function addProperty($name, $config)
    {
        if($config instanceof Property) {
            $property = $config;

        } else {
            if (is_string($config)) {
                $config = array('name' => $name, 'comment' => $config);
            } elseif (is_array($config) && !is_numeric($name)) {
                if(isset($config[0])) {
                    array_unshift($config, $name);
                } else {
                    $config['name'] = $name;
                }
            }
            $config['model'] = $this;
            $property = new Property($config);
        }

        return $this->properties[$property->getName()] = $property;
    }

    public function removeProperty($name)
    {
        if (empty($this->properties[$name])) {
            throw new \Exception("Model {$this->getName()} doesn't have property $name");
        }
        unset($this->properties[$name]);
    }


    /**
     * @return Property[]
     * @throws \Exception
     */
    public function getProperties()
    {
        $first = $this->getPk();

        $properties = $this->properties;

        foreach($this->behaviours as $behaviour) {
            foreach($behaviour->getProperties() as $property) {
                if(isset($properties[$property->getName()])) {
                    throw new Exception(sprintf("Duplicate property %s.%s", $this->getName(), $property->getName()));
                }
                $properties[$property->getName()] = $property;
            }
        }

        $pk = $other = array();
        foreach($properties as $property) {
            if(in_array($property->getName(), $first)) {
                $pk[$property->getName()] = $property;
            } else {
                $other[$property->getName()] = $property;
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

    /**
     * @param $name
     * @return Property
     */
    public function getProperty($name)
    {
        if(isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        foreach ($this->behaviours as $behaviour) {
            $property = $behaviour->getProperty($name);
            if($property) {
                return $property;
            }
        }

        throw new Exception(sprintf('Model %s has not property %s', $this->getName(), $name));
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasProperty($name)
    {
        if(isset($this->properties[$name])) {
            return true;
        }

        foreach ($this->behaviours as $behaviour) {
            $property = $behaviour->getProperty($name);
            if($property) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $reference \Cti\Storage\Component\Reference
     * @return Model
     */
    public function addReference($reference)
    {
        if ($reference->getSource() == $this->getName()) {
            $this->references['out'][$reference->getDestination()] = $reference;
        } elseif ($reference->getDestination() == $this->getName()) {
            $this->references['in'][$reference->getSource()] = $reference;
        } else {
            throw new \Exception("Invalid reference for model {$this->getName()}. Source: {$reference->getSource()},"
                ." destination: {$reference->getDestination()}");
        }
        return $this;
    }

    /**
     * @return \Cti\Storage\Behaviour\Behaviour[]
     */
    public function getBehaviours()
    {
        return $this->behaviours;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->class_name;
    }

    /**
     * @return string
     */
    public function getClassNameMany()
    {
        return $this->class_name_many;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return \Cti\Storage\Component\Index[]
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

    /**
     * @return \Cti\Storage\Component\Model[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->model_class;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Cti\Storage\Component\Reference[]
     */
    public function getReferences()
    {
        /**
         * In references property we have "table_name=>reference" array.
         * There can be same table in IN and in OUT, and merge will remove some reference.
         * Array_values used.
         */
        return array_merge(array_values($this->references['out']), array_values($this->references['in']));
    }


    /**
     * @return \Cti\Storage\Component\Reference[]
     */
    public function getOutReferences()
    {
        return $this->references['out'];
    }

    /**
     * @param String $modelName
     * @return \Cti\Storage\Component\Reference
     */
    public function getOutReference($modelName)
    {
        foreach($this->references['out'] as $reference) {
            if ($reference->getDestination() == $modelName) {
                return $reference;
            }
        }
    }

    /**
     * @return \Cti\Storage\Component\Reference[]
     */
    public function getInReferences()
    {
        return $this->references['in'];
    }

    /**
     * @param String $modelName
     * @return \Cti\Storage\Component\Reference
     */
    public function getInReference($modelName)
    {
        foreach($this->references['in'] as $reference) {
            if ($reference->getSource() == $modelName) {
                return $reference;
            }
        }
    }

    /**
     * @return string
     */
    public function getRepositoryClass()
    {
        return $this->repository_class;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    public function createSequence()
    {
        if ((bool)$this->getSequence()) {
            throw new \Exception("Model {$this->getName()} already have sequence");
        }
        $sequence_name = "sq_" . $this->getName();
        $this->sequence = new Sequence($sequence_name, $this);
    }

    public function removeSequence()
    {
        $this->sequence = null;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return array
     */
    public function getUsageList($schema)
    {
        $result = array($this->name);
        foreach($this->references as $src => $list) {
            foreach($list as $reference) {
                foreach(array($reference->getSource(), $reference->getDestination()) as $name) {
                    if(!in_array($name, $result)) {
                        $result[] = $name;
                    }
                    $model = $schema->getModel($name);
                    if($model->hasBehaviour('link')) {
                        $foreignNick = $model->getBehaviour('link')->getForeignModel($this)->getName();
                        if(!in_array($foreignNick, $result)) {
                            $result[] = $foreignNick;
                        }
                    }
                }
            }
        }
        sort($result);
        return $result;
    }
}