{var $source = $schema->getModel($reference->getSource())}
    /**
     * {$source->getComment()} Collection
     * @var {$source->getClassName()}[]
     */
    protected ${$source->getName()|pluralize};

