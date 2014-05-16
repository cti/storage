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

    public function testManyToManyCreateAndFind()
    {
        \DatabaseManager::generateFakeRecords();

        $admin = $this->personRepository->findOne(array(
            'login' => 'admin'
        ));

        $backend = $this->moduleRepository->findOne(array(
            'name' => 'Backend'
        ));

        /**
         * Add link through link repository
         */
        $link = $this->favoriteModuleLinkRepository->createLink($admin, $backend);
        $link->save();
        $this->assertNotNull($link);

        $links = $admin->getPersonFavoriteModuleLinks();
        $modules = array();
        foreach($links as $link) {
            $modules[] = $link->getFavoriteModule();
        }
        $this->assertContains($backend, $modules);

        /**
         * Add link through model methods
         */
        $backend->addModuleDeveloperLink($admin);

        $developed_modules = array();
        foreach($admin->getModuleDeveloperLinks() as $link) {
            $developed_modules[] = $link->getModule();
        }
        $this->assertContains($backend, $developed_modules);

        \DatabaseManager::clearTables();
    }

    public function testAdditionalPropertiesAndUpdate()
    {
        \DatabaseManager::generateFakeRecords();

        $admin = $this->personRepository->findOne(array(
            'login' => 'admin'
        ));
        $backend = $this->moduleRepository->findOne(array(
            'name' => 'Backend'
        ));
        $admin->addPersonFavoriteModuleLink($backend, array(
            'rating' => 50
        ));
        $link = $backend->getPersonFavoriteModuleLink($admin);
        $this->assertEquals(50, $link->getRating());

        /**
         * Move v_start of first row to past
         */
        $now = $this->dbal->fetchNow();
        $past = date('Y-m-d H:i:s', strtotime($now) - 60);
        $this->dbal->update('person_favorite_module_link', array('v_start' => $past), array("1" => "1"));

        $link->setRating(20);
        $link->save();

        $link = $admin->getPersonFavoriteModuleLink($backend);
        $this->assertEquals(20, $link->getRating());

        $rows = $this->dbal->fetchAll("select * from person_favorite_module_link order by v_end DESC");
        $this->assertCount(2, $rows);
        $this->assertEquals(20, $rows[0]['rating']);
        $this->assertEquals(50, $rows[1]['rating']);
        \DatabaseManager::clearTables();
    }

    public function testManyToManyDelete()
    {
        \DatabaseManager::generateFakeRecords();
        $admin = $this->personRepository->findOne(array(
            'login' => 'admin'
        ));
        $backend = $this->moduleRepository->findOne(array(
            'name' => 'Backend'
        ));

        $admin->addModuleDeveloperLink($backend);
        $links = $admin->getModuleDeveloperLinks();
        $this->assertCount(1, $links);
        $link = $links[0];
        $link->delete();

        $links = $admin->getModuleDeveloperLinks();
        $this->assertCount(0, $links);

        $rows = $this->dbal->fetchAll("select * from module_developer_link");
        $this->assertCount(1, $rows);
        $now = $this->dbal->fetchNow();
        $this->assertLessThanOrEqual(strtotime($now), $rows[0]['v_end']);

        \DatabaseManager::clearTables();
    }



} 