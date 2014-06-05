    /**
     * {$property->getComment()}

     * @var {$property->getType()}

     */
     protected ${$property->getName()};

{if $property->getRelation()?}
{var $relation = $property->getRelation()}
{var $foreignModel = $schema->getModel($relation->getDestination())}
{var $name = $relation->getDestinationAlias() ? $relation->getDestinationAlias() : $foreignModel->getName()}
    /**
     * {$name} property
     * @var {$foreignModel->getClassName()}

     */
     protected ${$name};
{/if}
