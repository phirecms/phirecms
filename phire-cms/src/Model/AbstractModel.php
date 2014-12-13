<?php

namespace Phire\Model;

abstract class AbstractModel implements \ArrayAccess
{

    /**
     * Model data array
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * Instantiate a model object
     *
     * @param  array $data
     * @return AbstractModel
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Return all model data as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Magic get method to return the value of data[$name].
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return (array_key_exists($name, $this->data)) ? $this->data[$name] : null;
    }

    /**
     * Magic set method to set the property to the value of data[$name].
     *
     * @param  string $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Return the isset value of data[$name].
     *
     * @param  string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Unset data[$name].
     *
     * @param  string $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * ArrayAccess offsetExists
     *
     * @param  mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * ArrayAccess offsetGet
     *
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * ArrayAccess offsetSet
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * ArrayAccess offsetUnset
     *
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

}