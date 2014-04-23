<?php

namespace Cti\Storage\Behaviour;

use Cti\Storage\Component\Model;
use Cti\Storage\Component\Property;

class Log extends Behaviour
{
    public function init(Model $model)
    {
        $this->properties = array(
            'v_start' => new Property(array(
                'behaviour' => true,
                'name' => 'v_start',
                'type' => 'timestamp',
                'primary' => true,
            )),
            'v_end' => new Property(array(
                'behaviour' => true,
                'name' => 'v_end',
                'type' => 'timestamp',
                'readonly' => true
            ))
        );
    }

    public function getPk()
    {
        return array('v_end');
    }
}