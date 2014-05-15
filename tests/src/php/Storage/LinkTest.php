<?php
namespace Storage;

class LinkTest extends \PHPUnit_Framework_TestCase
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
     * @var \Storage\Repository\PersonFavoriteModuleLinkRepository
     */
    protected $favoriteModuleLinkRepository;

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
        $this->favoriteModuleLinkRepository = $master->getPersonFavoriteModuleLinks();
        $this->dbal = getApplication()->getStorage()->getAdapter();
    }

    public function testHasOne()
    {
        \DatabaseManager::generateFakeRecords();

        $admin = $this->personRepository->findOne(array(
            'login' => 'admin'
        ));
        $backend = $this->moduleRepository->findOne(array(
            'name' => 'Backend'
        ));
        $admin->setDefaultModule($backend);
        $admin->save();
        $row = $this->dbal->fetchAssoc("select * from person where login = :login and v_end > :now", array(
            'login' => 'admin',
            'now' => $this->dbal->fetchNow(),
        ));
        $this->assertEquals($backend->getIdModule(), $admin->getIdModuleDefaultModule());
        $this->assertEquals($backend->getIdModule(), $row['id_module_default_module']);

        $user = $this->personRepository->findOne(array(
            'login' => 'user'
        ));
        $this->assertEquals($backend, $user->getDefaultModule());

        $user->setDefaultModule(null);
        $user->save();
        $row = $this->dbal->fetchAssoc("select * from person where login = :login and v_end > :now", array(
            'login' => 'user',
            'now' => $this->dbal->fetchNow(),
        ));
        $this->assertNull($row['id_module_default_module']);

        \DatabaseManager::clearTables();
    }

    public function testManyToManyCreate()
    {
        $this->markTestSkipped();
        \DatabaseManager::generateFakeRecords();

        $admin = $this->personRepository->findOne(array(
            'login' => 'admin'
        ));

        $backend = $this->moduleRepository->findOne(array(
            'name' => 'Backend'
        ));

        $link = $this->favoriteModuleLinkRepository->createLink($admin, $backend);
        $this->assertNotNull($link);

        $modules = $admin->getFavoriteModules();
        $this->assertContains($modules, $backend);


        \DatabaseManager::clearTables();
    }

    public function testManyToManyFind()
    {
        $this->markTestSkipped();
        \DatabaseManager::generateFakeRecords();

        \DatabaseManager::clearTables();
    }

    public function testManyToManyDelete()
    {
        $this->markTestSkipped();
        \DatabaseManager::generateFakeRecords();

        \DatabaseManager::clearTables();
    }



} 