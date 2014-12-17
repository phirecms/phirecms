<?php

namespace Phire\Controller\Modules;

use Phire\Controller\AbstractController;
use Phire\Form;
use Pop\Http\Response;

class IndexController extends AbstractController
{

    public function index()
    {
        $this->prepareView('modules/index.phtml');
        $this->view->title = 'Modules';
        $this->response->setBody($this->view->render());
        $this->send();
    }

}