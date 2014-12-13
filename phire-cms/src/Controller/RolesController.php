<?php

namespace Phire\Controller;

use Phire\Form;
use Pop\Http\Response;

class RolesController extends AbstractController
{

    public function index()
    {
        $this->prepareView('roles/index.phtml');
        $this->view->title = 'Roles';
        $this->response->setBody($this->view->render());
        $this->send();
    }

}