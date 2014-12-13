<?php

namespace Phire\Controller;

use Phire\Form;
use Pop\Auth;
use Pop\Http\Response;

class IndexController extends AbstractController
{

    public function index()
    {
        $this->prepareView('index.phtml');
        $this->view->title = 'Welcome to Phire';
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function login()
    {
        $this->prepareView('login.phtml');
        $this->view->title = 'Login';

        $form = new Form\Login();

        if ($this->request->isPost()) {
            $auth = new Auth\Auth(
                new Auth\Adapter\Table(
                    'Phire\Table\Users',
                    Auth\Auth::ENCRYPT_BCRYPT
                )
            );

            $form->setFieldValues($this->request->getPost(), [
                'strip_tags'   => null,
                'htmlentities' => [ENT_QUOTES, 'UTF-8']
            ], $auth);

            if ($form->isValid()) {
                $this->sess->user = [
                    'id'       => $auth->adapter()->getUser()->id,
                    'role_id'  => $auth->adapter()->getUser()->role_id,
                    'username' => $auth->adapter()->getUser()->username,
                    'email'    => $auth->adapter()->getUser()->email,
                ];
                Response::redirect(BASE_PATH . APP_URI);
                exit();
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function register()
    {
        echo 'Register.';
    }

    public function unsubscribe()
    {
        echo 'Unsubscribe.';
    }

    public function logout()
    {
        $this->sess->kill();
        Response::redirect(BASE_PATH . APP_URI . '/login');
    }

}