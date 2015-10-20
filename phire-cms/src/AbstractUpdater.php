<?php

namespace Phire;

use Phire\Table;

abstract class AbstractUpdater
{

    protected $resource        = null;
    protected $newUpdates      = [];
    protected $previousUpdates = [];

    public function __construct($resource)
    {
        $this->setResource($resource);

        $updates = null;

        if ($this->resource == 'phire') {
            $updates = Table\Config::findById('updates')->value;
        } else {
            $module = Table\Modules::findBy(['folder' => $this->resource]);
            if (isset($module->id)) {
                $updates = $module->updates;
            }
        }

        if (!empty($updates)) {
            $this->previousUpdates = explode('|', $updates);
        }
    }

    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getNewUpdates()
    {
        return $this->newUpdates;
    }

    public function getPreviousUpdates()
    {
        return $this->previousUpdates;
    }

    /**
     * Method to post update code for Phire or a module,
     * usually for database modification or file cleanup
     */
    public function runPost()
    {
        foreach ($this->newUpdates as $new) {
            if (!in_array($new, $this->previousUpdates)) {
                $method = 'update' . $new;
                if (method_exists($this, $method)) {
                    $this->$method();
                }
                $this->previousUpdates[] = $new;
            }
        }

        if ($this->resource == 'phire') {
            $updates = Table\Config::findById('updates');
            $updates->value = implode('|', $this->previousUpdates);
            $updates->save();

            $updated = Table\Config::findById('updated_on');
            $updated->value = date('Y-m-d H:i:s');
            $updated->save();
        } else {
            $module = Table\Modules::findBy(['folder' => $this->resource]);
            if (isset($module->id)) {
                $module->updates    = implode('|', $this->previousUpdates);
                $module->updated_on = date('Y-m-d H:i:s');
                $module->save();
            }
        }
    }

}
