<?php

namespace Cti\Storage\Component;

use Cti\Storage\Schema;

class Reference
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $destination;

    /**
     * @var string
     */
    protected $destination_alias;

    /**
     * @var string
     */
    protected $referenced_by;

    /**
     * @var string
     */
    protected $strategy = 'merge';

    /**
     * @var Property[]
     */
    protected $properties = array();

    /**
     * @param string $source
     * @param string $destination
     */
    function __construct($source, $destination)
    {
        $this->source = $source;
        $this->destination = $destination;

        $this->referenced_by = $source;
        $this->destination_alias = $destination;
    }

    /**
     * @param $name
     * @return $this
     */
    function usingAlias($name)
    {
        $this->destination_alias = $name;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    function referencedBy($name)
    {
        $this->referenced_by = $name;
        return $this;
    }

    /**
     * @param $strategy
     * @return $this
     */
    function setStrategy($strategy)
    {
        $this->strategy = $strategy;
        return $this;
    }

    /**
     * @param Schema $schema
     * @return $this
     */
    function process(Schema $schema)
    {
        $source = $schema->getModel($this->source);
        $destination = $schema->getModel($this->destination);

        foreach($destination->getPk() as $key) {
            $property = $destination->getProperty($key);
            $behaviour = $property->getBehaviour();
            if (!($behaviour instanceof \Cti\Storage\Behaviour\Log)) {
                $name = $this->getDestinationAlias() != $this->getDestination() ? $key . '_' . $this->getDestinationAlias(): $key;
                $comment = ucfirst($this->getDestinationAlias());
                if($this->getDestination() == $this->getDestinationAlias()) {
                    $comment = $schema->getModel($this->getDestination())->getComment();
                }
                $this->properties[$name] = $source->addProperty($name, new Property(array(
                    'name' => $name,
                    'foreignName' => $key,
                    'comment' => $comment,
                    'type' => $property->getType(),
                    'relation' => $this,
                    'readonly' => true,
                )));
            }
        }

        $destination->addReference($this);
        return $this;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @return string
     */
    public function getDestinationAlias()
    {
        return $this->destination_alias;
    }

    /**
     * @return \Cti\Storage\Component\Property[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getReferencedBy()
    {
        return $this->referenced_by;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}