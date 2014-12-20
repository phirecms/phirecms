<?php

namespace Phire\Controller\Modules;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Http\Response;
use Pop\Paginator\Paginator;

class IndexController extends AbstractController
{

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

        $config     = $this->application->config();
        $modulePath = (isset($config['module_path'])) ? $config['module_path'] : null;

        $this->prepareView('modules/index.phtml');
        $this->view->title      = 'Modules';
        $this->view->pages      = $pages;
        $this->view->newModules = $module->detectNew($modulePath);
        $this->view->modules    = $module->getAll(
            $this->application->modules(), $this->services['acl'],
            $limit, $this->request->getQuery('page'), $this->request->getQuery('sort')
        );

        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function install()
    {
        $config     = $this->application->config();
        $modulePath = (isset($config['module_path'])) ? $config['module_path'] : null;

        $module = new Model\Module();
        $module->install($this->services, $modulePath);

        Response::redirect(BASE_PATH . APP_URI . '/modules');
    }

    public function process()
    {
        $config     = $this->application->config();
        $modulePath = (isset($config['module_path'])) ? $config['module_path'] : null;

        $module = new Model\Module();
        $module->process($this->request->getPost(), $this->services, $modulePath);

        Response::redirect(BASE_PATH . APP_URI . '/modules');
    }

}