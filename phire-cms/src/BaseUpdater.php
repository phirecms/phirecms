<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire;

use Phire\Table;
use Pop\Archive\Archive;
use Pop\File\Dir;

/**
 * Phire BaseUpdater class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.0
 */
class BaseUpdater
{

    /**
     * Module
     * @var mixed
     */
    protected $module = null;

    /**
     * Previous updates
     * @var array
     */
    protected $previousUpdates = [];

    /**
     * Constructor
     *
     * @param  mixed $module
     * @return BaseUpdater
     */
    public function __construct($module = null)
    {
        $this->setModule($module);

        $updates = null;

        if (null === $this->module) {
            $updates = Table\Config::findById('updates')->value;
        } else {
            $module = Table\Modules::findBy(['name' => $this->module]);
            if (isset($module->id)) {
                $updates = $module->updates;
            }
        }

        if (!empty($updates)) {
            $this->previousUpdates = explode('|', $updates);
        }
    }

    /**
     * Set the module
     *
     * @param  mixed $module
     * @return BaseUpdater
     */
    public function setModule($module = null)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * Get the module
     *
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }
    
    /**
     * Get the previous updates
     *
     * @return array
     */
    public function getPreviousUpdates()
    {
        return $this->previousUpdates;
    }

    /**
     * Method to get update for one-click update
     *
     * @param string $module
     * @param string $new
     * @param string $old
     * @param int    $id
     * @return void
     */
    public function getUpdate($module = null, $new = null, $old = null, $id = null)
    {
        $docRoot = null;

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'];
        } else {
            $config = Table\Config::findById('document_root');
            if (isset($config->value)) {
                $docRoot = $config->value;
            }
        }

        if (null !== $docRoot) {
            if (null === $module) {
                if (file_exists($docRoot . CONTENT_PATH . '/assets/phire')) {
                    $dir = new Dir($docRoot . CONTENT_PATH . '/assets/phire');
                    $dir->emptyDir(true);
                }
                if (file_exists($docRoot . CONTENT_PATH . '/assets/default')) {
                    $dir = new Dir($docRoot . CONTENT_PATH . '/assets/default');
                    $dir->emptyDir(true);
                }
                if (file_exists($docRoot . CONTENT_PATH . '/assets/default-flat')) {
                    $dir = new Dir($docRoot . CONTENT_PATH . '/assets/default-flat');
                    $dir->emptyDir(true);
                }
                if (file_exists($docRoot . CONTENT_PATH . '/assets/default-top')) {
                    $dir = new Dir($docRoot . CONTENT_PATH . '/assets/default-top');
                    $dir->emptyDir(true);
                }
                if (file_exists($docRoot . CONTENT_PATH . '/assets/default-top-flat')) {
                    $dir = new Dir($docRoot . CONTENT_PATH . '/assets/default-top-flat');
                    $dir->emptyDir(true);
                }

                file_put_contents(
                    $docRoot . CONTENT_PATH . '/updates/phirecms.zip',
                    fopen('http://updates.phirecms.org/releases/phire/phirecms.zip', 'r')
                );

                $basePath = realpath($docRoot . CONTENT_PATH . '/updates/');

                $archive = new Archive($basePath . '/phirecms.zip');
                $archive->extract($basePath);
                unlink($docRoot . CONTENT_PATH . '/updates/phirecms.zip');

                $json = json_decode(stream_get_contents(fopen('http://updates.phirecms.org/releases/phire/phire.json', 'r')), true);

                foreach ($json as $file) {
                    if (!file_exists(__DIR__ . '/../' . $file) && !file_exists(dirname(__DIR__ . '/../' . $file))) {
                        mkdir(dirname(__DIR__ . '/../' . $file), 0755, true);
                    }
                    copy($docRoot . CONTENT_PATH . '/updates/phire-cms/' . $file, __DIR__ . '/../' . $file);
                }

                $dir = new Dir($docRoot . CONTENT_PATH . '/updates/phire-cms/');
                $dir->emptyDir(true);
            } else {
                if (file_exists($docRoot . MODULES_PATH . '/' . $module . '-' . $old . '.zip')) {
                    unlink($docRoot . MODULES_PATH . '/' . $module . '-' . $old . '.zip');
                }

                if (file_exists($docRoot . MODULES_PATH . '/' . $module . '-' . $old)) {
                    $dir = new Dir($docRoot . MODULES_PATH . '/' . $module . '-' . $old);
                    $dir->emptyDir(true);
                }

                if (file_exists($docRoot . CONTENT_PATH . '/assets/' . $module)) {
                    $dir = new Dir($docRoot . CONTENT_PATH . '/assets/' . $module);
                    $dir->emptyDir(true);
                }

                file_put_contents(
                    $docRoot . MODULES_PATH . '/' . $module . '-' . $new . '.zip',
                    fopen('http://updates.phirecms.org/releases/modules/' . $module . '-' . $new . '.zip', 'r')
                );

                $basePath = realpath($docRoot . MODULES_PATH . '/');
                $archive = new Archive($basePath . '/' . $module . '-' . $new . '.zip');
                $archive->extract($basePath);

                $mod = Table\Modules::findById($id);

                $assets = unserialize($mod->assets);
                if (isset($assets['info']['version'])) {
                    $assets['info']['version'] = $new;
                } else if (isset($assets['info']['Version'])) {
                    $assets['info']['Version'] = $new;
                } else if (isset($assets['info']['VERSION'])) {
                    $assets['info']['VERSION'] = $new;
                }

                $mod->file    = $module . '-' . $new . '.zip';
                $mod->folder  = $module . '-' . $new;
                $mod->assets  = serialize($assets);

                $mod->save();
            }
        }
    }

    /**
     * Method for post update code for Phire or for a module,
     * usually for database modification or file cleanup
     *
     * @return void
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
            $module = Table\Modules::findBy(['name' => $this->module]);
            if (isset($module->id)) {
                $module->updates    = implode('|', $this->previousUpdates);
                $module->updated_on = date('Y-m-d H:i:s');
                $module->save();
            }
        }
    }

}
