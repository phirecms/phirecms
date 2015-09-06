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
     * Get sort order
     *
     * @param  string $sort
     * @param  string $page
     * @param  string $ord
     * @return array
     */
    public function getSortOrder($sort = null, $page = null, $ord = 'ASC')
    {
        $field = 'id';
        $order = $ord;
        $sess  = null;

        if ((stripos(php_sapi_name(), 'cli') === false) || (stripos(php_sapi_name(), 'server') !== false)) {
            $sess = \Pop\Web\Session::getInstance();
        }

        if (null !== $sort) {
            if ((null !== $sess) && ($page != $sess->lastPage)) {
                if ($sort != $sess->lastSortField) {
                    $field = $sort;
                    $order = $ord;
                } else {
                    $field = $sess->lastSortField;
                    $order = $sess->lastSortOrder;
                }
            } else {
                $field = $sort;
                if ((null !== $sess) && isset($sess->lastSortOrder)) {
                    $order = ($sess->lastSortOrder == 'ASC') ? 'DESC' : 'ASC';
                } else {
                    $order = $ord;
                }
            }
        }

        if (null !== $sess) {
            $sess->lastSortField = $field;
            $sess->lastSortOrder = $order;
            $sess->lastPage      = $page;
        }

        return $field . ' ' . $order;
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