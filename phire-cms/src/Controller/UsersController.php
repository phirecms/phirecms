<?php

namespace Phire\Controller;

use Phire\Form;
use Pop\Http\Response;

class UsersController extends AbstractController
{

    public function index()
    {
        $this->prepareView('users/index.phtml');
        $this->view->title = 'Users';
        $this->response->setBody($this->view->render());
        $this->send();
    }

}