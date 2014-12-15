<?php

namespace Phire\Controller;

use Phire\Form;
use Phire\Model;

class ConfigController extends AbstractController
{

    public function index()
    {
        $config = new Model\Config();

        if ($this->request->isPost()) {
            $config->save($this->request->getPost());
            $config = new Model\Config();
        }

        $this->prepareView('config.phtml');
        $this->view->title    = 'Configuration';
        $this->view->overview = $config->overview;
        $this->view->config   = $config->config;

        $this->response->setBody($this->view->render());
        $this->send();
    }

}