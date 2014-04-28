<?php
namespace Component;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    private $application;
    /**
     * @var \Cti\Storage\Schema
     */
    private $schema;
    /**
     * @var \Cti\Storage\Component\Model
     */
    private $testModel;

    public function __construct()
    {
        $this->application = getApplication();
        $this->schema = $this->application->getSchema();
        parent::__construct();
    }

    public function testCreation()
    {
        $this->testModel = $testModel = $this->schema->createModel('test', 'Тестовая таблица', array(
            'description' => array(
                'comment' => 'Описание',
                'type' => 'text'
            ),
            'lineno' => array(
                'comment' => 'Номер',
                'type' => 'integer',
                'required' => true,
            ),
            'type' => 'Тип',
        ));
        $returnedModel = $this->schema->getModel("test");

        $this->assertNotEmpty($returnedModel);
        $this->assertEquals("\\Storage\\Model\\TestBase", $returnedModel->getModelClass());
    }

    public function testHasOne()
    {
        $testModel = $this->schema->getModel('test');
        $person = $this->schema->getModel('person');
        $testModel
            ->hasOne('person')
            ->usingAlias('owner')
            ->referencedBy('own_test');


    }

    public function testRemoving()
    {
        $this->schema->removeModel("test");
        $this->setExpectedException("Exception", "Model test was not yet defined");
        $this->schema->getModel("test");
    }

    public function testIndexCreateAndRemove()
    {
        $person = $this->schema->getModel("person");

        // create index
        $person->createIndex("hash", "salt");
        $indexMap = array();
        $hashSaltIndex = null;
        foreach($person->getIndexes() as $index) {
            $fields = $index->getFields();
            $indexMap[] = implode(':', $fields);
            if (in_array('hash', $fields) && in_array('salt', $fields)) {
                $hashSaltIndex = $index;
            }
        }
        $this->assertNotNull($hashSaltIndex);
        $this->assertContains("hash:salt", $indexMap);
        $this->assertCount(2, $indexMap);

        // remove index
        $person->removeIndex($hashSaltIndex);
        $this->assertCount(1, $person->getIndexes());
    }
}
