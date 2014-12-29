<?php

namespace Phire\Controller\Config;

use Phire\Controller\AbstractController;
use Phire\Model;
use Pop\Http\Response;

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
            Response::redirect(BASE_PATH . APP_URI . '/config?saved=' . time());
            exit();
        }

        $this->prepareView('config/index.phtml');
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