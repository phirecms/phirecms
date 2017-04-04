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
namespace Phire\Controller\Modules;

use Phire\Controller\AbstractController;
use Phire\Model;
use Pop\Paginator\Form as Paginator;
use Pop\Nav\Nav;

/**
 * Modules controller class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
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

        if ($module->hasPages($this->application->config()['pagination'])) {
            $limit = $this->application->config()['pagination'];
            $pages = new Paginator($module->getCount(), $limit);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->prepareView('modules/index.phtml');
        $this->view->title       = 'Modules';
        $this->view->pages       = $pages;
        $this->view->newModules  = $module->detectNew();
        $this->view->queryString = $this->getQueryString('sort');
        $this->view->modules     = $module->getAll($limit, $this->request->getQuery('page'), $this->request->getQuery('sort'));

        foreach ($this->view->modules as $module) {
            if ($this->application->isRegistered($module->name) && isset($this->application->modules[$module->name]->config()['nav.module'])) {
                $module->nav = new Nav(
                    [$this->application->modules[$module->name]->config()['nav.module']], ['top' => ['class' => 'module-nav']]
                );
                $module->nav->setBaseUrl(BASE_PATH . APP_URI);
                $module->nav->setAcl($this->application->services['acl']);
                $module->nav->setRole($this->application->services['acl']->getRole($this->sess->user->role));
                $module->nav->setIndent('                    ');
            }
        }

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

        $this->redirect(BASE_PATH . APP_URI . '/modules');
    }

}
