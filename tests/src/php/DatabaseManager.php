<?php

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Cti\Di\Reflection;

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
        $dbal = getApplication()->getStorage()->getAdapter();
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
        $user->setSalt('123456');
        $user->save();

        /**
         * Move old models start time to the past
         */
        $dbal->executeQuery("update person set v_start = :past where login = :login and v_end < '9999-12-31 23:59:59'",array(
            'past' => date("Y-m-d H:i:s", time() - 5000),
            'login' => 'user',
        ));

        /**
         * Clear repositories maps
         */

        foreach(array($master->getModules(), $master->getPersons()) as $repo) {
            $mapProperty = Reflection::getReflectionProperty(get_class($repo), 'map');
            $mapProperty->setAccessible(true);
            $mapProperty->setValue($repo, array());
            $mapProperty->setAccessible(false);
        }


    }
} 