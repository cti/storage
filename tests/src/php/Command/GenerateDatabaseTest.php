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
    protected $adapter;

    public function testSchemaConverter()
    {
        $application = getApplication();
        $manager = $application->getManager();
        $this->adapter = $manager->get('Cti\Storage\Adapter\DBAL');

        $generator = $application->getConsole()->find('generate:database');

        $this->clearDatabase();

        $input = new StringInput("generate:database --test");

        $output = new NullOutput;
        ob_start();
        $generator->run($input, $output);
        $sql = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($this->getExpectedMigrateSql(), $sql);
        $this->adapter->rollBack();
        $this->adapter->beginTransaction();
    }

    public function getExpectedMigrateSql()
    {
        if ($this->adapter->isOracle()) {
            return str_replace("\r","",
"CREATE SEQUENCE sq_person START WITH 1 MINVALUE 1 INCREMENT BY 1;
CREATE SEQUENCE sq_module START WITH 1 MINVALUE 1 INCREMENT BY 1;
CREATE TABLE person (id_person NUMBER(10) NOT NULL, v_end DATE NOT NULL, id_module_default_module NUMBER(10) DEFAULT NULL, hash VARCHAR2(255) DEFAULT NULL, login VARCHAR2(255) NOT NULL, salt VARCHAR2(255) DEFAULT NULL, status VARCHAR(1) DEFAULT NULL, v_start DATE NOT NULL, PRIMARY KEY(id_person, v_end));
CREATE INDEX IDX_34DCD176AA08CB10 ON person (login);
CREATE INDEX IDX_34DCD176E29C4A61 ON person (id_module_default_module);
COMMENT ON COLUMN person.id_person IS 'Identifier';
COMMENT ON COLUMN person.v_end IS 'Version end';
COMMENT ON COLUMN person.id_module_default_module IS 'Default_module link';
COMMENT ON COLUMN person.hash IS 'Полученный хэш';
COMMENT ON COLUMN person.login IS 'Имя пользователя';
COMMENT ON COLUMN person.salt IS 'Соль для вычисления хэша';
COMMENT ON COLUMN person.status IS 'Статус';
COMMENT ON COLUMN person.v_start IS 'Version start';
CREATE TABLE module (id_module NUMBER(10) NOT NULL, id_person_owner NUMBER(10) DEFAULT NULL, name VARCHAR2(255) DEFAULT NULL, PRIMARY KEY(id_module));
COMMENT ON COLUMN module.id_module IS 'Identifier';
COMMENT ON COLUMN module.id_person_owner IS 'Owner';
COMMENT ON COLUMN module.name IS 'Наименование';
CREATE TABLE person_favorite_module_link (id_module_favorite_module NUMBER(10) NOT NULL, id_person NUMBER(10) NOT NULL, v_end DATE NOT NULL, rating NUMBER(10) DEFAULT NULL, v_start DATE NOT NULL, PRIMARY KEY(id_module_favorite_module, id_person, v_end));
CREATE INDEX IDX_ABC434EACDFA5ACF ON person_favorite_module_link (id_module_favorite_module);
COMMENT ON COLUMN person_favorite_module_link.id_module_favorite_module IS 'Favorite_module';
COMMENT ON COLUMN person_favorite_module_link.id_person IS 'Пользователь';
COMMENT ON COLUMN person_favorite_module_link.rating IS 'Рейтинг';
CREATE TABLE module_developer_link (id_module NUMBER(10) NOT NULL, id_person_developer NUMBER(10) NOT NULL, v_end DATE NOT NULL, v_start DATE NOT NULL, PRIMARY KEY(id_module, id_person_developer, v_end));
CREATE INDEX IDX_B32214A82A1393C5 ON module_developer_link (id_module);
COMMENT ON COLUMN module_developer_link.id_module IS 'Модуль';
COMMENT ON COLUMN module_developer_link.id_person_developer IS 'Developer';
COMMENT ON COLUMN module_developer_link.v_end IS 'Version end';
COMMENT ON COLUMN module_developer_link.v_start IS 'Version start';
ALTER TABLE person ADD CONSTRAINT FK_34DCD176E29C4A61 FOREIGN KEY (id_module_default_module) REFERENCES module (id_module);
ALTER TABLE person_favorite_module_link ADD CONSTRAINT FK_ABC434EACDFA5ACF FOREIGN KEY (id_module_favorite_module) REFERENCES module (id_module);
ALTER TABLE module_developer_link ADD CONSTRAINT FK_B32214A82A1393C5 FOREIGN KEY (id_module) REFERENCES module (id_module);"
            );
        } elseif ($this->adapter->isSQLite()) {
            return str_replace("\r","",
"CREATE TABLE person (id_person INTEGER NOT NULL, v_end DATETIME NOT NULL, id_module_default_module INTEGER DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, login VARCHAR(255) NOT NULL, salt VARCHAR(255) DEFAULT NULL, v_start DATETIME NOT NULL, PRIMARY KEY(id_person, v_end));
CREATE INDEX IDX_34DCD176AA08CB10 ON person (login);
CREATE INDEX IDX_34DCD176E29C4A61 ON person (id_module_default_module);
CREATE TABLE module (id_module INTEGER NOT NULL, id_person_owner INTEGER DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id_module));
CREATE TABLE person_favorite_module_link (id_module_favorite_module INTEGER NOT NULL, id_person INTEGER NOT NULL, v_end DATETIME NOT NULL, rating INTEGER DEFAULT NULL, v_start DATETIME NOT NULL, PRIMARY KEY(id_module_favorite_module, id_person, v_end));
CREATE INDEX IDX_ABC434EACDFA5ACF ON person_favorite_module_link (id_module_favorite_module);
CREATE TABLE module_developer_link (id_module INTEGER NOT NULL, id_person_developer INTEGER NOT NULL, v_end DATETIME NOT NULL, v_start DATETIME NOT NULL, PRIMARY KEY(id_module, id_person_developer, v_end));
CREATE INDEX IDX_B32214A82A1393C5 ON module_developer_link (id_module);"
            );

        } elseif ($this->adapter->isPostgres()) {
            return str_replace("\r","",
"CREATE SEQUENCE sq_person INCREMENT BY 1 MINVALUE 1 START 1;
CREATE SEQUENCE sq_module INCREMENT BY 1 MINVALUE 1 START 1;
CREATE TABLE person (id_person INT NOT NULL, v_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id_module_default_module INT DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, login VARCHAR(255) NOT NULL, salt VARCHAR(255) DEFAULT NULL, status VARCHAR(1) DEFAULT NULL, v_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_person, v_end));
CREATE INDEX IDX_34DCD176AA08CB10 ON person (login);
CREATE INDEX IDX_34DCD176E29C4A61 ON person (id_module_default_module);
COMMENT ON COLUMN person.id_person IS 'Identifier';
COMMENT ON COLUMN person.v_end IS 'Version end';
COMMENT ON COLUMN person.id_module_default_module IS 'Default_module';
COMMENT ON COLUMN person.hash IS 'Полученный хэш';
COMMENT ON COLUMN person.login IS 'Имя пользователя';
COMMENT ON COLUMN person.salt IS 'Соль для вычисления хэша';
COMMENT ON COLUMN person.status IS 'Статус';
COMMENT ON COLUMN person.v_start IS 'Version start';
CREATE TABLE module (id_module INT NOT NULL, id_person_owner INT DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id_module));
COMMENT ON COLUMN module.id_module IS 'Identifier';
COMMENT ON COLUMN module.id_person_owner IS 'Owner';
COMMENT ON COLUMN module.name IS 'Наименование';
CREATE TABLE person_favorite_module_link (id_module_favorite_module INT NOT NULL, id_person INT NOT NULL, v_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, rating INT DEFAULT NULL, v_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_module_favorite_module, id_person, v_end));
CREATE INDEX IDX_ABC434EACDFA5ACF ON person_favorite_module_link (id_module_favorite_module);
COMMENT ON COLUMN person_favorite_module_link.id_module_favorite_module IS 'Favorite_module';
COMMENT ON COLUMN person_favorite_module_link.id_person IS 'Пользователь';
COMMENT ON COLUMN person_favorite_module_link.v_end IS 'Version end';
COMMENT ON COLUMN person_favorite_module_link.rating IS 'Рейтинг';
COMMENT ON COLUMN person_favorite_module_link.v_start IS 'Version start';
CREATE TABLE module_developer_link (id_module INT NOT NULL, id_person_developer INT NOT NULL, v_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, v_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id_module, id_person_developer, v_end));
CREATE INDEX IDX_B32214A82A1393C5 ON module_developer_link (id_module);
COMMENT ON COLUMN module_developer_link.id_module IS 'Модуль';
COMMENT ON COLUMN module_developer_link.id_person_developer IS 'Developer';
COMMENT ON COLUMN module_developer_link.v_end IS 'Version end';
COMMENT ON COLUMN module_developer_link.v_start IS 'Version start';
ALTER TABLE person ADD CONSTRAINT FK_34DCD176E29C4A61 FOREIGN KEY (id_module_default_module) REFERENCES module (id_module) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE person_favorite_module_link ADD CONSTRAINT FK_ABC434EACDFA5ACF FOREIGN KEY (id_module_favorite_module) REFERENCES module (id_module) NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE module_developer_link ADD CONSTRAINT FK_B32214A82A1393C5 FOREIGN KEY (id_module) REFERENCES module (id_module) NOT DEFERRABLE INITIALLY IMMEDIATE;"
            );
        }

    }

    public function clearDatabase()
    {
        $schemaManager = $this->adapter->getSchemaManager();

        foreach($schemaManager->listTables() as $table) {
            foreach($table->getForeignKeys() as $fk) {
                $schemaManager->dropForeignKey($fk->getName(), $table->getName());
            }
        }

        foreach($schemaManager->listTables() as $table) {
            $this->adapter->executeQuery("drop table {$table->getName()}");
        }

        // sqlite has no sequences
        if ($schemaManager->getDatabasePlatform()->getName() == 'sqlite') {
            return;
        }

        foreach($schemaManager->listSequences() as $sequence) {
            $schemaManager->dropSequence($sequence->getName());
        }
    }
}