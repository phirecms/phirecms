<?php

namespace Phire\Controller\Modules;

use Phire\Controller\AbstractController;
use Phire\Model;
use Pop\Paginator\Paginator;

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
     * Install action method
     *
     * @return void
     */
    public function install()
    {
        $module = new Model\Module();
        $module->install($this->services);

        $this->sess->setRequestValue('saved', true, 1);
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
        $module->update($id);

        $this->sess->setRequestValue('saved', true, 1);
        $this->redirect(BASE_PATH . APP_URI . '/modules');
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
            $this->sess->setRequestValue('removed', true, 1);
        } else {
            $this->sess->setRequestValue('saved', true, 1);
        }

        \Pop\Http\Response::redirect(BASE_PATH . APP_URI . '/modules');
        exit();
    }

}