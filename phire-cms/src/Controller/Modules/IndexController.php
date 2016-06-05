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
namespace Phire\Controller\Modules;

use Phire\Controller\AbstractController;
use Phire\Model;
use Pop\Paginator\Paginator;

/**
 * Modules Index Controller class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $module = new Model\Module();

        if ($module->hasPages($this->config->pagination)) {
            $limit = $this->config->pagination;
            $pages = new Paginator($module->getCount(), $limit);
            $pages->useInput(true);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->prepareView('phire/modules/index.phtml');
        $this->view->title      = 'Modules';
        $this->view->pages      = $pages;
        $this->view->newModules = $module->detectNew();
        $this->view->modules    = $module->getAll(
            $this->application->modules(), $this->services['acl'],
            $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
        );

        $this->view->moduleUpdates = $this->sess->updates->modules;

        $this->send();
    }

    /**
     * Upload action method
     *
     * @return void
     */
    public function upload()
    {
        if (($_FILES) && !empty($_FILES['upload_module']) && !empty($_FILES['upload_module']['name'])) {
            $module = new Model\Module();
            $module->upload($_FILES['upload_module']);
            $module->install($this->services);
            $this->sess->setRequestValue('saved', true);
        }

        $this->redirect(BASE_PATH . APP_URI . '/modules');
    }

    /**
     * Install action method
     *
     * @return void
     */
    public function install()
    {
        $module = new Model\Module();
        $module->install($this->services);

        $this->sess->setRequestValue('saved', true);
        $this->redirect(BASE_PATH . APP_URI . '/modules');
    }

    /**
     * Update action method
     *
     * @param  int $id
     * @return void
     */
    public function update($id)
    {
        $module = new Model\Module();
        $module->getById($id);

        if (isset($module->id) && isset($this->sess->updates->modules[$module->name]) &&
            (version_compare($module->version, $this->sess->updates->modules[$module->name]) < 0)) {
            if (($this->request->getQuery('update') == 2) &&
                is_writable(__DIR__ . '/../../../..' . CONTENT_PATH . '/modules') &&
                is_writable(__DIR__ . '/../../../..' . CONTENT_PATH . '/modules/' . $module->folder) &&
                is_writable(__DIR__ . '/../../../..' . CONTENT_PATH . '/modules/' . $module->folder . '.zip')) {
                clearstatcache();

                $updaterClass = $module->prefix . 'Updater';

                if (class_exists($updaterClass)) {
                    $updater = new $updaterClass($module->name);
                    $updater->runPost();
                }
                $mod = \Phire\Table\Modules::findById($id);
                if (isset($mod->id)) {
                    $mod->version = $this->sess->updates->modules[$module->name];
                    $mod->updated_on = date('Y-m-d H:i:s');
                    $mod->save();
                }

                $this->redirect(BASE_PATH . APP_URI . '/modules/complete/' . $id);
            } else if (($this->request->getQuery('update') == 1) &&
                is_writable(__DIR__ . '/../../../..' . CONTENT_PATH . '/modules') &&
                is_writable(__DIR__ . '/../../../..' . CONTENT_PATH . '/modules/' . $module->folder) &&
                is_writable(__DIR__ . '/../../../..' . CONTENT_PATH . '/modules/' . $module->folder . '.zip')) {
                $updater = new \Phire\Updater($module->name);
                $updater->getUpdate($module->name, $this->sess->updates->modules[$module->name], $module->version, $id);
                $this->redirect(BASE_PATH . APP_URI . '/modules/update/' . $id . '?update=2');
            } else {
                $this->prepareView('phire/modules/update.phtml');
                $this->view->title                 = 'Update ' . $module->name;
                $this->view->module_id             = $module->id;
                $this->view->module_name           = $module->name;
                $this->view->module_update_version = $this->sess->updates->modules[$module->name];

                if (is_writable(__DIR__ . '/../../../..' . CONTENT_PATH . '/modules') &&
                    is_writable(__DIR__ . '/../../../..' . CONTENT_PATH . '/modules/' . $module->folder) &&
                    is_writable(__DIR__ . '/../../../..' . CONTENT_PATH . '/modules/' . $module->folder . '.zip')) {
                    $this->view->writable = true;
                } else {
                    $this->view->writable = false;
                }

                $this->send();
            }
        } else {
            $this->redirect(BASE_PATH . APP_URI . '/modules');
        }
    }

    /**
     * Complete action method
     *
     * @param  int $id
     * @return void
     */
    public function complete($id)
    {
        $module = new Model\Module();
        $module->getById($id);

        if (isset($module->id)) {
            $this->prepareView('phire/modules/update.phtml');
            $this->view->title       = 'Update Module ' . $module->folder . ' : Complete!';
            $this->view->complete    = true;
            $this->view->module_name = $module->folder;
            $this->view->version     = $module->version;
            $this->send();
        } else {
            $this->redirect(BASE_PATH . APP_URI . '/modules');
        }
    }

    /**
     * Process action method
     *
     * @return void
     */
    public function process()
    {
        $module = new Model\Module();
        $module->process($this->request->getPost(), $this->services);

        if (null !== $this->request->getPost('rm_modules')) {
            $this->sess->setRequestValue('removed', true);
        } else {
            $this->sess->setRequestValue('saved', true);
        }

        \Pop\Http\Response::redirect(BASE_PATH . APP_URI . '/modules');
        exit();
    }

}