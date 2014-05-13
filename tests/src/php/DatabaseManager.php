<?php

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class DatabaseManager
{
    public static function syncDatabase()
    {
        $application = getApplication();
        $generator = $application->getConsole()->find("generate:database");

        $input = new StringInput("generate:database");
        $output = new NullOutput();
        ob_start();
        $generator->run($input, $output);
        ob_end_clean();

        self::clearTables();
    }

    public static function clearTables()
    {
        $dbal = getApplication()->getStorage()->getAdapter();
        foreach($dbal->getSchemaManager()->listTables() as $table) {
            $dbal->executeQuery("TRUNCATE {$table->getName()} CASCADE");
        }
        $dbal->commit();
        $dbal->beginTransaction();
    }

    /**
     * Create fake records (Admin in persons, Backend in modules)
     */
    public static function generateFakeRecords()
    {
        $master = getApplication()->getStorage()->getMaster();
        $admin = $master->getPersons()->create(array(
            'hash' => '123',
            'login' => 'admin',
            'salt' => '123',
        ))->save();

        $backend = $master->getModules()->create(array(
            'id_person_owner' => $admin->getIdPerson(),
            'name' => 'Backend',
        ))->save();

        $user = $master->getPersons()->create(array(
            'hash' => '123',
            'login' => 'user',
            'salt' => '321',
            'id_module_default_module' => $backend->getIdModule()
        ))->save();
        sleep(1);
        $user->setSalt('123456');
        $user->save();


    }
} 