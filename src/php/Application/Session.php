<?php

namespace Application;

/**
 * Class Session
 * @package Web
 */
class Session
{
    /**
     * Session name
     * @var string
     */
    public $name;

    /**
     * Container
     * @var array
     */
    protected $data = array();

    /**
     * Constructor.
     * Set default session name.
     */
    public function __construct()
    {
        $this->name = session_name();
    }

    /**
     * Init session
     */
    public function init()
    {
        @session_name($this->name);
        @session_start();
        $this->data = &$_SESSION;
    }

    /**
     * Get session id
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * Check session has name
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $_SESSION);
    }

    /**
     * @param $name
     * @return bool
     */
    public function contains($name)
    {
        return array_key_exists($name, $_SESSION);
    }

    /**
     * Get data array
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Get session value
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function __get($name, $default = null)
    {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }

    /**
     * Set value
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (is_null($value)) {
            unset($this->data[$name]);
        } else {
            $this->data[$name] = $value;
        }
    }

    /**
     * Clear all session values
     */
    public function clear()
    {
        foreach (array_keys($this->data) as $key) {
            unset($this->data[$key]);
        }
    }
}