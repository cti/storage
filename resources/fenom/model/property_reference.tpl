{var $source = $schema->getModel($reference->getSource())}
{if !$source->getBehaviour('link')}
    /**
     * {$source->getComment()} Collection
     * @var {$source->getClassName()}[]
     */
    protected ${$source->getName()|pluralize};

{/if}