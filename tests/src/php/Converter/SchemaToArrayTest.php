<?php
namespace Converter;

class SchemaToArrayTest extends \PHPUnit_Framework_TestCase
{

    public function testConverting()
    {
        $schema = getApplication()->getStorage()->getSchema();
        $array = $schema->asArray();
//        var_export($array);
//        var_export($this->getResultArray());
//        exit;
        $this->assertEquals($this->getResultArray(), $array);

    }

    public function getResultArray()
    {
        return array(
            'models' => array(
                'person' => array(
                    'is_link' => false,
                    'has_log' => true,
                    'pk' => array('id_person', 'v_end'),
                    'properties' => array(
                        'id_person' => array(
                            'comment' => 'identifier',
                            'type' => 'integer',
                            'required' => true,
                        ),
                        'login' => array(
                            'comment' => 'Имя пользователя',
                            'type' => 'string',
                            'required' => true,
                        ),
                        'salt' => array(
                            'comment' => 'Соль для вычисления хэша',
                            'type' => 'string',
                            'required' => false,
                        ),
                        'hash' => array(
                            'comment' => 'Полученный хэш',
                            'type' => 'string',
                            'required' => false,
                        ),
                        'id_module_default_module' => array(
                            'comment' => 'default_module link',
                            'type' => 'integer',
                            'required' => false,
                        ),
                        'v_start' => array(
                            'comment' => '',
                            'type' => 'datetime',
                            'required' => true,
                        ),
                        'v_end' => array(
                            'comment' => '',
                            'type' => 'datetime',
                            'required' => true,
                        ),
                    ),
                    'references' => array(
                        array(
                            'destination' => 'module',
                            'destination_alias' => 'default_module',
                            'properties' => array('id_module_default_module'),
                        ),
                    ),
                ),
                'module' => array(
                    'is_link' => false,
                    'has_log' => false,
                    'pk' => array('id_module'),
                    'properties' => array(
                        'id_module' => array(
                            'comment' => 'identifier',
                            'type' => 'integer',
                            'required' => true,
                        ),
                        'name' => array(
                            'comment' => 'Наименование',
                            'type' => 'string',
                            'required' => false,
                        ),
                        'id_person_owner' => array(
                            'comment' => 'owner link',
                            'type' => 'integer',
                            'required' => false,
                        )

                    ),
                    'references' => array(
                        array(
                            'destination' => 'person',
                            'destination_alias' => 'owner',
                            'properties' => array('id_person_owner'),
                        )
                    )
                ),
                'person_favorite_module_link' => array(
                    'pk' => array('id_module_favorite_module', 'id_person', 'v_end'),
                    'has_log' => true,
                    'is_link' => true,
                    'properties' => array(
                        'id_person' => array(
                            'comment' => 'person link',
                            'type' => 'integer',
                            'required' => true,
                        ),
                        'id_module_favorite_module' => array(
                            'comment' => 'favorite_module link',
                            'type' => 'integer',
                            'required' => true,
                        ),
                        'rating' => array(
                            'comment' => 'Рейтинг',
                            'type' => 'integer',
                            'required' => false,
                        ),
                        'v_start' => array(
                            'comment' => '',
                            'type' => 'datetime',
                            'required' => true,
                        ),
                        'v_end' => array(
                            'comment' => '',
                            'type' => 'datetime',
                            'required' => true,
                        ),
                    ),
                    'references' => array(
                        array(
                            'destination' => 'person',
                            'destination_alias' => 'person',
                            'properties' => array('id_person'),
                        ),
                        array(
                            'destination' => 'module',
                            'destination_alias' => 'favorite_module',
                            'properties' => array('id_module_favorite_module'),
                        ),
                    )
                ),
                'module_developer_link' => array(
                    'pk' => array('id_module', 'id_person_developer', 'v_end'),
                    'has_log' => true,
                    'is_link' => true,
                    'properties' => array(
                        'id_module' => array(
                            'comment' => 'module link',
                            'type' => 'integer',
                            'required' => true,
                        ),
                        'id_person_developer' => array(
                            'comment' => 'developer link',
                            'type' => 'integer',
                            'required' => true,
                        ),
                        'v_start' => array(
                            'comment' => '',
                            'type' => 'datetime',
                            'required' => true,
                        ),
                        'v_end' => array(
                            'comment' => '',
                            'type' => 'datetime',
                            'required' => true,
                        ),

                    ),
                    'references' => array(
                        array(
                            'destination' => 'module',
                            'destination_alias' => 'module',
                            'properties' => array('id_module'),
                        ),
                        array(
                            'destination' => 'person',
                            'destination_alias' => 'developer',
                            'properties' => array('id_person_developer'),
                        ),
                    )
                )
            ),
        );
    }

} 