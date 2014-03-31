<?php

namespace Nekufa\Storage\Query;

use Exception;
use Iterator;

/**
 * Select
 * @package Storage
 */
class Select implements Iterator
{
    /**
     * @inject
     * @var  Storage\Database
     */
    public $database;

    const STATE_QUERY = 'query';
    const STATE_ITERATION = 'iterator';

    const FETCH_ONE = '1';
    const FETCH_ALL = '*';

    private $state;
    private $fetch;

    private $table;

    public static function create($table) 
    {
        return new static;
    }

    public function __construct($table) {
        $this->table = $table;
        $this->fetch = self::FETCH_ALL;
        $this->state = self::STATE_QUERY;
    }

    private function updateState($state)
    {
        if($this->state != $state) {
            $this->state = $state;
            if($state == self::STATE_QUERY) {
                unset($this->data);

            } else {
                if($this->fetch == self::FETCH_ALL) {
                    $this->data = $this->database->fetchRows($this->getQuery(), $this->getParams());

                } else {
                    $this->data = array(
                        $this->database->fetchRow($this->getQuery(), $this->getParams())
                    );
                }
            }
        }
        return $this;
    }

    public function setFetchMode($fetch) {
        if(!in_array($fetch, array(self::FETCH_ALL, self::FETCH_ONE))) {
            throw new Exception(sprintf("Unknown fetch mode %s", $fetch));
        }
        $this->fetch = $fetch;
        return $this;
    }

    public function where($condition, $param = null)
    {
        if(isset($this->where[$condition])) {
            if($this->where[$condition] != func_get_args()) {
                throw new Exception(sprintf("Duplicate condition %s", $condition));
            }
        }
        $this->updateState(self::STATE_QUERY)->where[$condition] = func_get_args();
        return $this;
    }

    public function rewind()
    {
        reset($this->updateState(self::STATE_ITERATION)->data);
    }
  
    public function current()
    {
        return current($this->updateState(self::STATE_ITERATION)->data);
    }
  
    public function key() 
    {
        return key($this->updateState(self::STATE_ITERATION)->data);
    }
  
    public function next() 
    {
        return next($this->updateState(self::STATE_ITERATION)->data);
    }
  
    public function valid()
    {
        $key = key($this->updateState(self::STATE_ITERATION)->data);
        return ($key !== NULL && $key !== FALSE);
    }

    public function execute()
    {
        if($this->fetch == self::FETCH_ONE) {
            $data = $this->updateState(self::STATE_ITERATION)->data;
            return isset($data[0]) ? $data[0] : null;
        }
        return $this->updateState(self::STATE_ITERATION)->data;
    }

    public function getQuery()
    {
        $conditions = array();
        foreach($this->where as $args) {
            $conditions[] = array_shift($args);
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