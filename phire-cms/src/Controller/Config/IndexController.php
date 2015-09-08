<?php

namespace Phire\Controller\Config;

use Phire\Controller\AbstractController;
use Phire\Model;

class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $config = new Model\Config();

        if ($this->request->isPost()) {
            $config->save($this->request->getPost());
            $this->sess->setRequestValue('saved', true, 1);
            $this->redirect(BASE_PATH . APP_URI . '/config');
        }

        $this->prepareView('phire/config/index.phtml');
        $this->view->title    = 'Configuration';
        $this->view->overview = $config->overview;
        $this->view->config   = $config->config;
        $this->send();
    }

    /**
     * JSON action method
     *
     * @param  string $format
     * @return void
     */
    public function json($format)
    {
        $json = [
            'format' => date(str_replace('_', '/', urldecode($format)))
        ];
        $this->send(200, ['Content-Type' => 'application/json'], json_encode($json, JSON_PRETTY_PRINT));
    }

}