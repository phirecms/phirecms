<?php

namespace Phire;

use Phire\Table;
use Pop\Archive\Archive;
use Pop\File\Dir;

abstract class AbstractUpdater
{

    protected $resource        = null;
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

    public function getPreviousUpdates()
    {
        return $this->previousUpdates;
    }

    /**
     * Method to run one-click system update (for Phire only)
     */
    public function runUpdate()
    {
        file_put_contents(
            __DIR__ . '/../..' . CONTENT_PATH . '/updates/phirecms.zip',
            fopen('http://updates.phirecms.org/releases/phire/phirecms.zip', 'r')
        );

        $basePath = realpath(__DIR__ . '/../..' . CONTENT_PATH . '/updates/');

        $archive  = new Archive($basePath . '/phirecms.zip');
        $archive->extract($basePath);
        unlink(__DIR__ . '/../..' . CONTENT_PATH . '/updates/phirecms.zip');

        $json = json_decode(stream_get_contents(fopen('http://updates.phirecms.org/releases/phire/phire.json', 'r')), true);

        foreach ($json as $file) {
            echo 'Updating: ' . $file . '<br />' . PHP_EOL;
            copy(__DIR__ . '/../..' . CONTENT_PATH . '/updates/phire-cms/' . $file, __DIR__ . '/../' . $file);
        }

        $dir = new Dir(__DIR__ . '/../..' . CONTENT_PATH . '/updates/phire-cms/');
        $dir->emptyDir(true);
    }

    /**
     * Method to post update code for Phire or for a module,
     * usually for database modification or file cleanup
     */
    public function runPost()
    {
        $i      = (count($this->previousUpdates) > 0) ? max($this->previousUpdates) + 1 : 1;
        $method = 'update' . $i;

        while (method_exists($this, $method)) {
            $this->$method();
            $this->previousUpdates[] = $i;
            $i++;
            $method = 'update' . $i;
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
