<?php

namespace Direct;
use ___PHPSTORM_HELPERS\static;

/**
 * Class Transaction
 * @package Direct
 */
class Request
{
    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $method;

    /**
     * @var string
     */
    public $type;

    /**
     * @var int
     */
    public $tid;

    /**
     * @var array
     */
    public $data;

    /**
     * @param $data
     * @return static
     */
    public static function create($data)
    {
        return new static($data->action, $data->method, $data->tid, $data->type, isset($data->data) ? $data->data : array());
    }

    /**
     * @param string $action
     * @param string $method
     * @param int $tid
     * @param string $type
     * @param array $data
     */
    function __construct($action, $method, $tid, $type, $data)
    {
        $this->action = $action;
        $this->method = $method;
        $this->tid = $tid;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return int
     */
    public function getTid()
    {
        return $this->tid;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Response
     */
    public function generateResponse()
    {
        return new Response($this->getAction(), $this->getMethod(), $this->getTid());
    }
}