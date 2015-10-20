<?php

namespace Phire;

use Phire\Table;
use Pop\Archive\Archive;
use Pop\File\Dir;

abstract class AbstractUpdater
{

    protected $module          = null;
    protected $previousUpdates = [];

    public function __construct($module = null)
    {
        $this->setModule($module);

        $updates = null;

        if (null === $this->module) {
            $updates = Table\Config::findById('updates')->value;
        } else {
            $module = Table\Modules::findBy(['folder' => $this->module]);
            if (isset($module->id)) {
                $updates = $module->updates;
            }
        }

        if (!empty($updates)) {
            $this->previousUpdates = explode('|', $updates);
        }
    }

    public function setModule($module = null)
    {
        $this->module = $module;
        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getPreviousUpdates()
    {
        return $this->previousUpdates;
    }

    /**
     * Method to get update for one-click update
     *
     * @param string $module
     */
    public function getUpdate($module = null)
    {
        if (null === $module) {
            file_put_contents(
                __DIR__ . '/../..' . CONTENT_PATH . '/updates/phirecms.zip',
                fopen('http://updates.phirecms.org/releases/phire/phirecms.zip', 'r')
            );

            $basePath = realpath(__DIR__ . '/../..' . CONTENT_PATH . '/updates/');

            $archive = new Archive($basePath . '/phirecms.zip');
            $archive->extract($basePath);
            unlink(__DIR__ . '/../..' . CONTENT_PATH . '/updates/phirecms.zip');

            $json = json_decode(stream_get_contents(fopen('http://updates.phirecms.org/releases/phire/phire.json', 'r')), true);

            foreach ($json as $file) {
                echo 'Updating: ' . $file . '<br />' . PHP_EOL;
                copy(__DIR__ . '/../..' . CONTENT_PATH . '/updates/phire-cms/' . $file, __DIR__ . '/../' . $file);
            }

            $dir = new Dir(__DIR__ . '/../..' . CONTENT_PATH . '/updates/phire-cms/');
            $dir->emptyDir(true);
        } else {
            if (file_exists(__DIR__ . '/../..' . CONTENT_PATH . '/modules/' . $module . '.zip')) {
                unlink(__DIR__ . '/../..' . CONTENT_PATH . '/modules/' . $module . '.zip');
            }

            if (file_exists(__DIR__ . '/../..' . CONTENT_PATH . '/modules/' . $module)) {
                $dir = new Dir(__DIR__ . '/../..' . CONTENT_PATH . '/modules/' . $module);
                $dir->emptyDir(true);
            }

            file_put_contents(
                __DIR__ . '/../..' . CONTENT_PATH . '/modules/' . $module . '.zip',
                fopen('http://updates.phirecms.org/releases/modules/' . $module . '.zip', 'r')
            );

            $basePath = realpath(__DIR__ . '/../..' . CONTENT_PATH . '/modules/');
            $archive = new Archive($basePath . '/' . $module . '.zip');
            $archive->extract($basePath);
        }
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

        if (null === $this->module) {
            $updates = Table\Config::findById('updates');
            $updates->value = implode('|', $this->previousUpdates);
            $updates->save();

            $updated = Table\Config::findById('updated_on');
            $updated->value = date('Y-m-d H:i:s');
            $updated->save();
        } else {
            $module = Table\Modules::findBy(['folder' => $this->module]);
            if (isset($module->id)) {
                $module->updates    = implode('|', $this->previousUpdates);
                $module->updated_on = date('Y-m-d H:i:s');
                $module->save();
            }
        }
    }

}