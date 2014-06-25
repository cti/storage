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
                'behaviour' => $this,
                'name' => 'v_start',
                'comment' => 'Version start',
                'type' => 'datetime',
                'primary' => true,
                'required' => true,
            )),
            'v_end' => new Property(array(
                'behaviour' => $this,
                'name' => 'v_end',
                'comment' => 'Version end',
                'type' => 'datetime',
                'readonly' => true,
                'required' => true,
            ))
        );
    }

    public function getPk()
    {
        return array('v_end');
    }
}