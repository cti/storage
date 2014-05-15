{var $source = $schema->getModel($reference->getSource())}
{var $rb = $reference->getReferencedBy()}
    /**
     * @var {$source->getModelClass()}[]
     */
     protected ${$rb|pluralize};
     {$reference->getSource()}
     {$reference->getDestination()}