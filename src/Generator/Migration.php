<?php

namespace Cti\Storage\Generator;

class Migration
{
    public $class;
    public $timestamp;

    public function __toString()
    {
        $classname = $this->class . date('_Ymd_His', $this->timestamp);
        $date = date('d.m.Y H:i:s', $this->timestamp);

return <<<STR
<?php

namespace Storage\Migration;

use Cti\Storage\Schema;

/**
 * Migration was generated at $date
 */
class $classname 
{
    public function process(Schema \$schema)
    {

    }

}
STR;
    }
    
}