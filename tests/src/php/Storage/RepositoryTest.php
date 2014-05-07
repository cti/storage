<?php

namespace Storage;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Storage\Master
     */
    protected $master;

    /**
     * @var \Cti\Storage\Adapter\DBAL
     */
    protected $dbal;

    /**
     * @var \Storage\Repository\PersonRepository
     */
    protected $personRepository;

    /**
     * @var \Storage\Repository\ModuleRepository
     */
    protected $moduleRepository;

    public function setUp()
    {
        $this->master = getApplication()->getStorage()->getMaster();
        $this->dbal = getApplication()->getStorage()->getAdapter();
        $this->syncDatabase();
        $this->personRepository = $this->master->getPersons();
        $this->moduleRepository = $this->master->getModules();
    }

    public function testCreate()
    {
        /**
         * Test person record with log and id behaviours
         */
        $admin = $this->personRepository->create(array(
            'login' => 'admin',
            'salt' => '123',
            'hash' => crypt('password', '123'),
        ));

        $this->assertNotNull($admin);
        $this->assertEquals('Storage\\Model\\PersonBase', get_class($admin));
        $this->assertEquals('admin', $admin->getLogin());

        $admin->save();
        $row = $this->dbal->fetchAssoc("select * from person where login = 'admin'");
        $this->assertNotNull($row);
        $this->assertNotNull($row['id_person']);
        $this->assertNotNull($row['v_end']);

        /**
         * Test module record without log
         */
        $backend = $this->moduleRepository->create(array(
            'name' => 'Backend'
        ));

        $backend->save();
        $this->assertNotNull($backend->getIdModule());

        $row = $this->dbal->fetchAssoc("select * from module where name = 'Backend'");
        $this->assertNotNull($row);
        $this->assertNotNull($row['id_module']);
        $this->assertArrayNotHasKey('v_end', $row);
        $this->clearTables();
    }

    public function testUpdate()
    {
        /**
         * Test update of Log model. Need to be 2 records after update. One of them with v_end in past.
         */
        $admin = $this->personRepository->create(array(
            'login' => 'admin',
            'salt' => '123',
            'hash' => crypt('password', '123'),
        ));
        $admin->save();
        sleep(1);
        $admin->setLogin('username');
        $admin->save();

        $rows = $this->dbal->fetchAll("select * from person where id_person = :id_person order by v_end desc", array(
            'id_person' => $admin->getIdPerson()
        ));


        $this->assertCount(2, $rows);
        $old = $rows[1];
        $new = $rows[0];

        $now = strtotime($this->dbal->fetchNow());
        $this->assertLessThanOrEqual($now, strtotime($old['v_end']));
        $this->assertLessThanOrEqual($now, strtotime($old['v_start']));

        $this->assertGreaterThanOrEqual(strtotime($new['v_start']), strtotime($old['v_end']));
//        $this->assertGreaterThan(time() + 5000*365*24*3600, strtotime($new['v_end'])); // 5000 years forward
        $endDate = new \DateTime($new['v_end']);
        $startDate = new \DateTime("9999-12-31 23:59:59");
        $this->assertEquals(0, $startDate->diff($endDate)->s);


        /**
         * Test update of not Log model. Need to be 1 record after update.
         */
        $backend = $this->moduleRepository->create(array(
            'name' => 'Backend'
        ));
        $backend->save();
        $firstId = $backend->getIdModule(); // First saved Id, need to be the same after update
        $backend->setName("Frontend");
        $backend->save();
        $rows = $this->dbal->fetchAll("select * from module");
        $this->assertCount(1, $rows);
        $module = $rows[0];
        $this->assertEquals('Frontend', $module['name']);
        $this->assertEquals($firstId, $module['id_module']);

        $this->clearTables();

    }

    public function testRemove()
    {
        /**
         * Test remove of log model. After delete need to be 2 versions. All of them with v_end in past.
         */
        $admin = $this->personRepository->create(array(
            'login' => 'admin',
            'salt' => '123',
            'hash' => crypt('password', '123'),
        ));
        $admin->save();
        $admin->setLogin('username');
        sleep(1);
        $admin->save();
        sleep(1);
        $admin->delete();
        $now = time() + 1;

        $rows = $this->dbal->fetchAll("select * from person where id_person = :id_person", array(
            'id_person' => $admin->getIdPerson()
        ));
        $this->assertCount(2, $rows);
        foreach($rows as $row) {
            $this->assertLessThanOrEqual($now, strtotime($row['v_end']));
        }

        /**
         * Test remove of not log model
         */
        $backend = $this->moduleRepository->create(array(
            'name' => 'Backend'
        ));
        $backend->save();
        $backend->delete();
        $rows = $this->dbal->fetchAll("select * from module");
        $this->assertCount(0, $rows);

        $this->clearTables();
    }

    public function testRemoveUnsavedModel()
    {
        $admin = $this->personRepository->create(array(
            'login' => 'admin',
            'salt' => '123',
            'hash' => crypt('password', '123'),
        ));
        $this->setExpectedException("Exception","Model \\Storage\\Model\\PersonBase can't be deleted. It is unsaved");
        $admin->delete();
    }


    /**
     * To use models and repos we need to have database
     */
    protected function syncDatabase()
    {
        $generator = getApplication()->getConsole()->find("generate:database");

        $input = new StringInput("generate:database");
        $output = new NullOutput();
        ob_start();
        $generator->run($input, $output);
        ob_end_clean();

        /**
         * Clear tables in database
         */
        $this->dbal->disableConstraints();
        foreach($this->dbal->getSchemaManager()->listTables() as $table) {
            $this->dbal->executeQuery("delete from {$table->getName()}");
        }
        $this->dbal->enableConstraints();
    }

    public function clearTables()
    {
        foreach(array('person','module') as $table) {
            $this->dbal->executeQuery("delete from $table");
        }
        $this->dbal->commit();
        $this->dbal->beginTransaction();
    }
}