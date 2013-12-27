<?php

namespace Di;

use RangeException;

/**
 * Class Configuration
 * @package Di
 */
class Configuration
{
    /**
     * @var array
     */
    protected $delimiter = array('.', '\\');

    /**
     * @var object
     */
    protected $data;

    /**
     * @param array $data
     */
    function __construct($data = null)
    {
        $this->data = (object) array();
        if($data) {
            $this->merge($data);
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value) 
    {
        $this->merge(array($key => $value));
    }

    /**
     * @param $data
     */
    public function merge($data)
    {
        $this->mergeWithObject($this->data, $data);
    }

    /**
     * @param $object
     * @param $data
     * @throws \RangeException
     */
    protected function mergeWithObject($object, $data)
    {
        foreach ($data as $key => $value) {
            $keys = $this->parseKey($key);
            $current_object = $object;
            $length = count($keys);
            foreach($keys as $i => $k) {
                if($k == '' && !is_numeric($k)) {
                    throw new RangeException("Error Processing $key property");
                }
                if(!isset($current_object->$k)) {
                    $current_object->$k = (object) array();
                }
                if($i != $length - 1) {
                    $current_object = $current_object->$k;
                }
            }
            if(is_array($value)) {
                $this->mergeWithObject($current_object->$k, $value);
            } else {
                $current_object->$k = $value;
            }
        }
    }

    /**
     * @param $string
     * @return array
     */
    function parseKey($string) 
    {
        static $keys = array();
        if(!isset($keys[$string])) {
            foreach ($this->delimiter as $delimiter) {
                $result = explode($delimiter, $string);
                if(count($result) > 1) {
                    $keys[$string] = $result;
                    break;
                }
            }
            if(!isset($keys[$string])) {
                $keys[$string] = array($string);
            }
        }
        return $keys[$string];
    }

    /**
     * @param $name
     * @return mixed
     */
    function get($name)
    {
        $current_object = $this->data;
        foreach($this->parseKey($name) as $key) {
            if(!isset($current_object->$key)) {
                return (object) array();
                break;
            }
            $current_object = $current_object->$key;
        }
        return $current_object;
    }
}