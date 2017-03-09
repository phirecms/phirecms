<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;
use Pop\Dir\Dir;
use Pop\Http\Upload;

/**
 * Module model class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Module extends AbstractModel
{

    /**
     * Get all modules
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return \Pop\Db\Record\Collection
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $order   = $this->getSortOrder($sort, $page);
        $options = ['order'  => $order];

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $options['offset'] = $page;
            $options['limit']  = $limit;
        }

        return Table\Modules::findAll($options);
    }


    /**
     * Get module by ID
     *
     * @param  int $id
     * @return void
     */
    public function getById($id)
    {
        $module = Table\Modules::findById($id);
        if (isset($module->id)) {
            $data = $module->toArray();
            $data['assets'] = unserialize($data['assets']);
            $this->data = array_merge($this->data, $data);
        }
    }

    /**
     * Detect new modules
     *
     * @param  boolean $count
     * @return mixed
     */
    public function detectNew($count = true)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/modules';
        $installed   = [];
        $newModules  = [];

        if (file_exists($modulesPath)) {
            $modules = Table\Modules::findAll();
            foreach ($modules as $module) {
                $installed[] = $module->file;
            }

            $dir = new Dir($modulesPath, ['filesOnly' => true]);

            foreach ($dir as $file) {
                if (((substr($file, -4) == '.zip') || (substr($file, -4) == '.tgz') ||
                     (substr($file, -7) == '.tar.gz') || (substr($file, -4) == '.tbz') ||
                     (substr($file, -8) == '.tar.bz2')) && !in_array($file, $installed)
                ) {
                    $newModules[] = $file;
                }
            }
        }

        return ($count) ? count($newModules) : $newModules;
    }

    /**
     * Upload module
     *
     * @param  array $file
     * @return void
     */
    public function upload($file)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/modules';
        $upload = new Upload($modulesPath);
        $upload->upload($file);
    }


    /**
     * Install modules
     *
     * @param  \Pop\Service\Locator $services
     * @throws \Exception
     * @return void
     */
    public function install(\Pop\Service\Locator $services)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/modules';
    }

    /**
     * Uninstall modules
     *
     * @param  array                $ids
     * @param  \Pop\Service\Locator $services
     * @return void
     */
    public function uninstall($ids, $services)
    {
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/modules';
    }

    /**
     * Process modules
     *
     * @param  array                $post
     * @param  \Pop\Service\Locator $services
     * @return void
     */
    public function process($post, \Pop\Service\Locator $services)
    {
        foreach ($post as $key => $value) {
            if (strpos($key, 'active_') !== false) {
                $id     = substr($key, (strrpos($key, '_') + 1));
                $module = Table\Modules::findById((int)$id);
                if (isset($module->id)) {
                    $module->active = (int)$value;
                    $module->order  = (int)$post['order_' . $id];
                    $module->save();
                }
            }
        }

        if (isset($post['rm_modules']) && (count($post['rm_modules']) > 0)) {
            $this->uninstall($post['rm_modules'], $services);
        }
    }

    /**
     * Determine if list of modules has pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\Modules::findAll(null, Table\Modules::AS_ARRAY)->count() > $limit);
    }

    /**
     * Get count of modules
     *
     * @return int
     */
    public function getCount()
    {
        return Table\Modules::findAll(null, Table\Modules::AS_ARRAY)->count();
    }

}