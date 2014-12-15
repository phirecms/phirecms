<?php

namespace Phire\Controller;

use Phire\Form;
use Phire\Model;
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

                Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
                exit();
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function register($id = 2001)
    {
        $this->prepareView('register.phtml');
        $this->view->title = 'Register';
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function verify($id, $hash)
    {
        $user = new Model\User();
        $this->prepareView('verify.phtml');
        $this->view->title  = 'Verify';
        $this->view->result = $user->verify($id, $hash);
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function unsubscribe()
    {
        $this->prepareView('unsubscribe.phtml');
        $this->view->title = 'Unsubscribe';

        $form = new Form\Unsubscribe();

        if ($this->request->isPost()) {
            $form->setFieldValues($this->request->getPost(), [
                'strip_tags'   => null,
                'htmlentities' => [ENT_QUOTES, 'UTF-8']
            ]);

            if ($form->isValid()) {
                $user = new Model\User();
                $user->unsubscribe($form->getFields());
                Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
                exit();
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function logout()
    {
        $this->sess->kill();
        Response::redirect(BASE_PATH . APP_URI . '/login');
    }

}