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
} 