<?php

namespace Phire\Controller\Config;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;

class IndexController extends AbstractController
{

    public function index()
    {
        $config = new Model\Config();

        if ($this->request->isPost()) {
            $config->save($this->request->getPost());
            $config = new Model\Config();
        }

        $this->prepareView('config/index.phtml');
        $this->view->title    = 'Configuration';
        $this->view->overview = $config->overview;
        $this->view->config   = $config->config;

        $this->response->setBody($this->view->render());
        $this->send();
    }

}