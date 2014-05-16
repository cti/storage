{var $linkModel = $schema->getModel($reference->getSource())}
{var $oppositeModel = $generator->getOppositeModel($linkModel)}
{if $linkModel->getBehaviour('link')}
    /**
     * @return {$linkModel->getModelClass()}[]
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
     * @param {$oppositeModel->getModelClass()}

     * @return {$linkModel->getModelClass()}

     */
    public function get{$linkModel->getClassName()}({$oppositeModel->getModelClass()} ${$oppositeModel->getName()})
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
     * @param {$oppositeModel->getModelClass()} ${$oppositeModel->getName()}

     * @param array $data
     * @return {$linkModel->getModelClass()}

     * @throws \Exception
     */
    public function add{$linkModel->getClassName()}({$oppositeModel->getModelClass()} ${$oppositeModel->getName()}, $data = array())
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

{/if}