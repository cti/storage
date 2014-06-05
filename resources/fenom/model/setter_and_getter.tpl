{var $name = $property->getName()}
    /**
     * {$property->getComment()}

     * @return {$property->getType()}

     */
    public function {$property->getGetter()}()
    {
       return $this->{$name};
    }

    /**
     * Set {$property->getComment()}

     * @param {$property->getType()}

     * @return {$model->getModelClass()}

     */
    public function {$property->getSetter()}(${$name})
    {
        if (!isset($this->_changes['{$name}'])) {
            $this->_changes['{$name}'] = array(
                'old' => $this->{$name}
            );
        }
        $this->_changes['{$name}']['new'] = ${$name};
        $this->{$name} = ${$name};
        return $this;
    }
{if $property->getRelation()?}
{var $relation = $property->getRelation()}
{var $foreignModel = $schema->getModel($relation->getDestination())}
{var $name = $relation->getDestinationAlias() ? $relation->getDestinationAlias() : $foreignModel->getName()}
    /**
     * Get {$name}

     * @return {$foreignModel->getClassName()}

     */
    public function get{$name|camelcase}()
    {
        if (!$this->{$name}) {
            $master = $this->getRepository()->getMaster();
            $this->{$name} = $master->getRepository('{$relation->getDestination()}')->findByPk(array(
    {foreach $relation->getProperties() as $relationProperty}
                '{$relationProperty->getForeignName()}' => $this->{$relationProperty->getGetter()}(),
    {/foreach}
            ));
        }
        return $this->{$name};
    }
{if !$model->getBehaviour('link')}
    /**
     * Set {$name}

     * @param {$foreignModel->getClassName()} ${$name}

     * @return {$model->getModelClass()}

     */
    public function set{$name|camelcase}({$foreignModel->getClassName()} ${$name} = null)
    {
    {foreach $relation->getProperties() as $relationProperty}

        $this->{$relationProperty->getSetter()}(null);
    {/foreach}

        if (${$name}) {
{foreach $relation->getProperties() as $relationProperty}
            $this->{$relationProperty->getSetter()}(${$name}->{$foreignModel->getProperty($relationProperty->getForeignName())->getGetter()}());
{/foreach}
        }
        $this->{$name} = null;
        return $this;
    }

{/if}
{/if}