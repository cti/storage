<?php

namespace Command;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class GenerateDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Cti\Storage\Adapter\DBAL
     */
    private $adapter;

    public function testSchemaConverter()
    {
        $application = getApplication();
        $generator = $application->getConsole()->find('generate:database');

        $input = new StringInput("generate:database --test");

        $output = new NullOutput;
        ob_start();
        $generator->run($input, $output);
        $sql = ob_get_contents();
        ob_end_clean();
        $this->assertEquals($this->getExpectedMigrateSql(), $sql);
    }

    public function getExpectedMigrateSql()
    {
        return str_replace("\r","",
"CREATE TABLE person (id_person INTEGER NOT NULL, v_end DATETIME NOT NULL, id_module_default_module INTEGER DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, login VARCHAR(255) NOT NULL, salt VARCHAR(255) DEFAULT NULL, v_start DATETIME NOT NULL, PRIMARY KEY(id_person, v_end))
CREATE INDEX IDX_34DCD176AA08CB10 ON person (login)
CREATE INDEX IDX_34DCD176E29C4A61 ON person (id_module_default_module)
CREATE TABLE module (id_module INTEGER NOT NULL, id_person_owner INTEGER DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id_module))
CREATE INDEX IDX_C24262840978CCA ON module (id_person_owner)
CREATE TABLE person_favorite_module_link (id_module_favorite_module INTEGER NOT NULL, id_person INTEGER NOT NULL, v_end DATETIME NOT NULL, rating INTEGER DEFAULT NULL, v_start DATETIME NOT NULL, PRIMARY KEY(id_module_favorite_module, id_person, v_end))
CREATE INDEX IDX_ABC434EA12EB649B ON person_favorite_module_link (id_person)
CREATE INDEX IDX_ABC434EACDFA5ACF ON person_favorite_module_link (id_module_favorite_module)
CREATE TABLE module_developer_link (id_module INTEGER NOT NULL, id_person_developer INTEGER NOT NULL, v_end DATETIME NOT NULL, v_start DATETIME NOT NULL, PRIMARY KEY(id_module, id_person_developer, v_end))
CREATE INDEX IDX_B32214A82A1393C5 ON module_developer_link (id_module)
CREATE INDEX IDX_B32214A83E583DE1 ON module_developer_link (id_person_developer)"
        );

    }
}