{var $linkModel = $schema->getModel($reference->getSource())}
{var $oppositeModel = $generator->getOppositeModel($linkModel)}
{if $linkModel->getBehaviour('link')}
    /**
     * @return {$linkModel->getClassName()}[]
     */
    public function get{$linkModel->getClassName()|pluralize}()
    {
        $linkRepository = $this->getRepository()->getMaster()->get{$linkModel->getClassName()|pluralize}();
        return $linkRepository->findAll(array(
{foreach $reference->getProperties() as $property}
            '{$property->getName()}' => $this->{$model->getProperty($property->getForeignName())->getGetter()}(),
{/foreach}
        ));
    }

    /**
     * @param {$oppositeModel->getClassName()}

     * @return {$linkModel->getClassName()}

     */
    public function get{$linkModel->getClassName()}({$oppositeModel->getClassName()} ${$oppositeModel->getName()})
    {
        $linkRepository = $this->getRepository()->getMaster()->get{$linkModel->getClassName()|pluralize}();
        return $linkRepository->findOne(array(
{foreach $reference->getProperties() as $property}
            '{$property->getName()}' => $this->{$model->getProperty($property->getForeignName())->getGetter()}(),
{/foreach}
{foreach $oppositeModel->getInReference($linkModel->getName())->getProperties() as $property}
            '{$property->getName()}' => ${$oppositeModel->getName()}->{$oppositeModel->getProperty($property->getForeignName())->getGetter()}(),
{/foreach}
        ));
    }

    /**
     * @param {$oppositeModel->getClassName()} ${$oppositeModel->getName()}

     * @param array $data
     * @return {$linkModel->getClassName()}

     * @throws \Exception
     */
    public function add{$linkModel->getClassName()}({$oppositeModel->getClassName()} ${$oppositeModel->getName()}, $data = array())
    {
        if ($this->get{$linkModel->getClassName()}(${$oppositeModel->getName()})) {
            throw new \Exception("{$oppositeModel->getName()} already linked to {$model->getName()}");
        }
        $linkRepository = $this->getRepository()->getMaster()->get{$linkModel->getClassName()|pluralize}();
{foreach $reference->getProperties() as $property}
        $data['{$property->getName()}'] = $this->{$model->getProperty($property->getForeignName())->getGetter()}();
{/foreach}
{foreach $oppositeModel->getInReference($linkModel->getName())->getProperties() as $property}
        $data['{$property->getName()}'] = ${$oppositeModel->getName()}->{$oppositeModel->getProperty($property->getForeignName())->getGetter()}();
{/foreach}
        return $linkRepository->create($data)->save();
    }

{else}
    /**
     * Get {$linkModel->getComment()} Collection
     * @return {$linkModel->getClassName()}[]
     */
    public function get{$linkModel->getClassName()|pluralize}() {
        $query = array(
{foreach $reference->getProperties() as $property}
            '{$property->getName()}' => $this->{$model->getProperty($property->getForeignName())->getGetter()}(),
{/foreach}
        );
        ${$linkModel->getName()}_repository = $this->getRepository()->getMaster()->get{$linkModel->getClassName()|pluralize}();
        return ${$linkModel->getName()}_repository->findAll($query);
    }
{/if}