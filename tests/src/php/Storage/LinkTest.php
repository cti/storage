<?php
namespace Storage;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Storage\Master
     */
    protected $master;

    /**
     * @var \Cti\Storage\Adapter\DBAL
     */
    protected $dbal;

    public function setUp()
    {
        $application = getApplication();
        $this->master = $application->getStorage()->getMaster();
        $this->dbal = $application->getStorage()->getAdapter();
        \DatabaseManager::syncDatabase();
    }

    public function testHasOne()
    {
        $this->markTestSkipped();
        $admin = $this->master->getPersons()->create(array(
            'login' => 'admin',
            'salt' => '123',
            'hash' => crypt('password', '123'),
        ));
        $admin->save();

        $backend = $this->master->getModules()->create(array(
            'name' => 'Backend'
        ));
        $backend->save();

        /**
         * Link admin with default module Backend
         */
        $admin->setDefaultModule($backend);
        $admin->save();

        $this->assertEquals($backend->getIdModule(), $admin->getIdModuleDefaultModule());
        $row = $this->dbal->fetchAssoc("select  * from person order by v_end desc");
        $this->assertEquals($backend->getIdModule(), $row['id_module_default_module']);

        /**
         * Link backend with owner Admin
         */
        $backend->setOwner($admin);
        $backend->save();

        $this->assertEquals($admin->getIdPerson(), $backend->getIdPersonOwner());
        $row = $this->dbal->fetchAssoc("select * from module");
        $this->assertEquals($admin->getIdPerson(), $row['id_person_owner']);

        $this->assertCount(1,$admin->getOwnModules());
        $this->assertEquals($backend, $admin->getOwnModules()[0]);

        \DatabaseManager::clearTables();

    }

    public function testLinks()
    {
        $this->markTestSkipped();

    }
}