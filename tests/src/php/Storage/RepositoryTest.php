<?php
namespace Storage;

use Cti\Di\Reflection;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Storage\Repository\PersonRepository
     */
    protected $personRepository;

    /**
     * @var \Storage\Repository\ModuleRepository
     */
    protected $moduleRepository;

    /**
     * @var \Cti\Storage\Adapter\DBAL
     */
    protected $dbal;

    public function setUp()
    {
        \DatabaseManager::syncDatabase();
        $master = getApplication()->getStorage()->getMaster();
        $this->personRepository = $master->getPersons();
        $this->moduleRepository = $master->getModules();
        $this->dbal = getApplication()->getStorage()->getAdapter();
    }

    public function testCreate()
    {
        /**
         * Test model create logic
         */
        $adminModel = $this->personRepository->create(array(
           'login' => 'admin',
            'salt' => '123',
            'hash' => crypt('123', '123'),
        ));
        $this->assertNotNull($adminModel);
        $this->assertEquals('admin', $adminModel->getLogin());
    }

    public function testSave()
    {
        $adminModel = $this->personRepository->create(array(
            'login' => 'admin',
            'salt' => '123',
            'hash' => crypt('123', '123'),
        ));
        $adminModel->save();

        /**
         * Check row existing in database
         */
        $row = $this->dbal->fetchAssoc("select * from person where login = :login", array(
            'login' => 'admin'
        ));
        $this->assertNotNull($row);

        /**
         * Test version behaviour in person model
         */
        $this->assertEquals('9999-12-31 23:59:59', $row['v_end']);
        $dbTime = $this->dbal->fetchNow();
        /**
         * Difference between $dbNow and v_start time of model need to be less than 5 seconds
         */
        $this->assertLessThan(5, abs(strtotime($dbTime) - strtotime($row['v_start'])));

        /**
         * Test not version model
         */
        $backendModule = $this->moduleRepository->create(array(
            'name' => 'backend'
        ));
        $backendModule->save();

        $row = $this->dbal->fetchAssoc("select * from module where name = :name", array(
            'name' => 'backend',
        ));
        $this->assertNotNull($row);
        $this->assertEquals('backend', $row['name']);

        /**
         * Test repository store for models
         */
        $mapProperty = Reflection::getReflectionProperty(get_class($this->personRepository), 'map');
        $mapProperty->setAccessible(true);
        $map = $mapProperty->getValue($this->personRepository);
        $mapProperty->setAccessible(false);

        $modelKey = $this->personRepository->makeKey($adminModel);
        $this->assertEquals($adminModel->getIdPerson().':'.$adminModel->getVEnd(), $modelKey);

        /**
         * Admin model need to be in map
         */
        $this->assertContains($adminModel, $map);
        \DatabaseManager::clearTables();
    }

    public function testFind()
    {
        \DatabaseManager::generateFakeRecords();
        $admin = $this->personRepository->findOne(array(
            'login' => 'admin'
        ));
        $this->assertNotNull($admin);
        $this->assertEquals('admin', $admin->getLogin());
        $persons = $this->personRepository->findAll();

        /**
         * There are 3 rows in table, but one of them have 2 versions
         * Need to fetch 2 actual persons
         */
        $this->assertCount(2, $persons, "Old model in result");

        /**
         * Test getting of old person model
         */
        $minuteAgo = date('Y-m-d H:i:s', strtotime($this->dbal->fetchNow()) - 60);
        $models = $this->personRepository->findAll(array(
            'login' => 'user'
        ), $minuteAgo);
        $this->assertCount(1, $models);
        $oldPerson = $models[0];
        $this->assertEquals('321', $oldPerson->getSalt());

        \DatabaseManager::clearTables();
    }

    public function testSyncFindWithMap()
    {
        \DatabaseManager::generateFakeRecords();
        $admin = $this->personRepository->findOne(array(
            'login' => 'admin'
        ));
        $admin->setSalt("test_salt");

        $secondInstanceOfAdmin = $this->personRepository->findOne(array(
            'login' => 'admin'
        ));
        $this->assertEquals('test_salt', $secondInstanceOfAdmin->getSalt());

        $records = $this->personRepository->findAll(array(
            'login' => 'admin'
        ));
        $foundWithFindAll = array_shift($records);
        $this->assertEquals('test_salt', $foundWithFindAll->getSalt());

        $mapProperty = Reflection::getReflectionProperty(get_class($this->personRepository), 'map');
        $mapProperty->setAccessible(true);
        $map = $mapProperty->getValue($this->personRepository);
        $this->assertCount(1, $map);
        $mapProperty->setAccessible(false);

        \DatabaseManager::clearTables();
    }

    public function testDelete()
    {
        $this->markTestSkipped();
        \DatabaseManager::generateFakeRecords();
        \DatabaseManager::clearTables();
    }
} 