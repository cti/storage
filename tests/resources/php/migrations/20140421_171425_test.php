<?php

namespace Storage\Migration;

use Cti\Storage\Schema;

/**
 * Migration was generated at 21.04.2014 17:14:25
 */
class Test_20140421_171425 
{
    public function process(Schema $schema)
    {
        $person = $schema->createModel('person', 'Пользователь', array(
            'login' => 'Имя пользователя',
            'salt'  => 'Соль для вычисления хэша',
            'hash'  => 'Полученный хэш',
        ));

        $person->createIndex('login');
        $person->addBehaviour('log');

        $module = $schema->createModel('module', 'Модуль', array(
            'name' => 'Наименование'
        ));

        // favorite modules link
        $favorite_module = $schema->createLink(array(
            $person,
            'favorite_module' => $module,
        ));

        $favorite_module->addProperty('rating', array(
            'comment' => 'Рейтинг', 
            'type'    =>  'integer'
        ));

        // module developers link
        $schema->createLink(array(
            $module,
            'developer' => $person,
        ));

        // default user module
        $schema->getModel('person')
            ->hasOne('module')
            ->usingAlias('default_module')
            ->referencedBy('default_user')
        ;

        // module owner
        $module
            ->hasOne('person')
            ->usingAlias('owner')
            ->referencedBy('own_module')
        ;
    }
}