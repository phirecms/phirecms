<?php

namespace Phire\Controller\Config;

use Phire\Controller\AbstractController;
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

    public function json($format)
    {
        $json = [
            'format' => date(str_replace('_', '/', urldecode($format)))
        ];
        $this->response->setBody(json_encode($json, JSON_PRETTY_PRINT));
        $this->send(200, ['Content-Type' => 'application/json']);
    }

}