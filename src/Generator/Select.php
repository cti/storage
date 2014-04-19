<?php

namespace Cti\Storage\Generator;

use Cti\Util\String;

class Select
{
    public $model;

    public function __toString()
    {
        $classname = $this->model->class_name .'Select';
        $table = $this->model->name;

        $methods = array();
        foreach($this->model->indexes as $index) {
            $methods[] = $this->renderIndex($index);
        }


        $methods = count($methods) ? PHP_EOL . implode(PHP_EOL.PHP_EOL, $methods) : '';

return <<<STR
<?php

namespace Storage\Query;

use Storage\Query\Select;

/** 
 * ATTENTION! DO NOT CHANGE! THIS CODE IS REGENERATED
 */
class $classname extends Select
{
    public function __construct() 
    {
        parent::__construct('$table');
    }
$methods
}
STR;
    }

    public function renderIndex($index)
    {

        $fields = $index->getFields();

        sort($fields);

        $name = array();
        $params = array();
        $where = array();

        foreach($fields as $field) {
            $name[] = String::convertToCamelCase($field);
            $params[] = $field;
            $where[] = "->where('$field = :$field', array(
                '$field' => \$$field
            ))";
        }

        $name = 'by' . implode('And', $name);
        $params = '$' . implode(', $', $params);

        $where = implode(PHP_EOL.'            ', $where);

        return <<<STR
    public function $name($params)
    {
        return \$this
            $where;
    }
STR;
    }
    
}