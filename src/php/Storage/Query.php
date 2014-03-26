<?php

namespace Storage;

use Exception;
use Iterator;

/**
 * Query
 * @package Storage
 */
abstract class Query implements Iterator
{
    /**
     * @inject
     * @var  Storage\Database
     */
    public $database;

    private $mode;
    private $data;

    const MODE_QUERY = 'QUERY';
    const MODE_ITERATOR = 'ITERATOR';

    public $table;

    public static function create() 
    {
        return new static;
    }

    private function updateMode($mode)
    {
        if($this->mode != $mode) {
            $this->mode = $mode;
            if($mode == self::MODE_QUERY) {
                unset($this->data);

            } else {
                $this->data = $this->database->fetchRows(
                    $this->getQuery(), 
                    $this->getParams()
                );
            }
        }
        return $this;
    }

    public function where($condition, $param = null)
    {
        if(isset($this->where[$condition])) {
            if($this->where[$condition] != func_get_args()) {
                throw new Exception(sprintf("Duplicate condition %s", $condition));
            }
        }
        $this->updateMode(self::MODE_QUERY)->where[$condition] = func_get_args();
        return $this;
    }

    public function rewind()
    {
        reset($this->updateMode(self::MODE_ITERATOR)->data);
    }
  
    public function current()
    {
        return current($this->updateMode(self::MODE_ITERATOR)->data);
    }
  
    public function key() 
    {
        return key($this->updateMode(self::MODE_ITERATOR)->data);
    }
  
    public function next() 
    {
        return next($this->updateMode(self::MODE_ITERATOR)->data);
    }
  
    public function valid()
    {
        $key = key($this->updateMode(self::MODE_ITERATOR)->data);
        return ($key !== NULL && $key !== FALSE);
    }

    public function getQuery()
    {
        $conditions = array();
        foreach($this->where as $args) {
            $conditions[] = array_shift($args);
        }

        if(!$this->table) {
            throw new \Exception("Table property required!");
        }

        return sprintf("select * from %s where %s", $this->table, implode(' and ', $conditions));
    }

    public function getParams()
    {
        $params = array();
        foreach($this->where as $args) {
            array_shift($args);
            if(count($args) == 1 && is_array($args[0])) {
                $args = $args[0];
            }
            foreach($args as $k => $v) {
                if(!is_numeric($k)) {
                    $params[$k] = $v;
                    
                } else {
                    $params[] = $v;
                }
            }
        }
        return $params;
    }
}