<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;

class Phire extends \Phire\Model\AbstractModel
{

    /**
     * Modules array
     */
    protected $modules = array();

    /**
     * Get session object
     *
     * @return \Pop\Web\Session
     */
    public function getSession()
    {
        return \Pop\Web\Session::getInstance();
    }

    /**
     * Check is a module is loaded
     *
     * @param  string $name
     * @return boolean
     */
    public function isLoaded($name)
    {
        return isset($this->modules[strtolower($name)]);
    }

    /**
     * Lazy load a module
     *
     * @param  string $name
     * @param  string $module
     * @return self
     */
    public function loadModule($name, $module)
    {
        $this->modules[strtolower($name)] = $module;
        return $this;
    }

    /**
     * Unload a module
     *
     * @param  string $name
     * @return self
     */
    public function unloadModule($name)
    {
        if (isset($this->modules[strtolower($name)])) {
            unset($this->modules[strtolower($name)]);
        }
        return $this;
    }

    /**
     * Get module model
     *
     * @param  string $name
     * @param  mixed  $args
     * @throws \Exception
     * @return mixed
     */
    public function module($name, $args = null)
    {
        $name = strtolower($name);
        if (!isset($this->modules[$name])) {
            throw new \Exception('That module has not been loaded.');
        }

        if (is_string($this->modules[$name])) {
            $class = $this->modules[$name];
            if (null !== $args) {
                if (!is_array($args)) {
                    $args = array($args);
                }
                $reflect = new \ReflectionClass($class);
                $result = $reflect->newInstanceArgs($args);
            } else {
                $result = new $class;
            }
            $this->modules[$name] = $result;
        }

        return $this->modules[$name];
    }

}

