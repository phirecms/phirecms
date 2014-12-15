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

    public function register($id)
    {
        $role = new Model\Role();

        if ($role->canRegister($id)) {
            $this->prepareView('register.phtml');
            $this->view->title = 'Register';

            $form = new Form\Register($id);

            if ($this->request->isPost()) {
                $form->setFieldValues($this->request->getPost(), [
                    'strip_tags'   => null,
                    'htmlentities' => [ENT_QUOTES, 'UTF-8']
                ]);

                if ($form->isValid()) {
                    $fields = $form->getFields();
                    $role->getById($id);
                    $fields['verified'] = (int)!($role->verification);
                    if ($role->approval) {
                        $fields['role_id'] = null;
                    }

                    $user = new Model\User();
                    $user->save($fields);

                    $this->view->success = true;
                } else {
                    $this->view->form = $form;
                }
                $this->response->setBody($this->view->render());
                $this->send();
            } else {
                $this->view->form = $form;
                $this->response->setBody($this->view->render());
                $this->send();
            }
        } else {
            Response::redirect(BASE_PATH . APP_URI);
        }
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

    public function forgot()
    {
        $this->prepareView('forgot.phtml');
        $this->view->title = 'Forgot your Password?';

        $form = new Form\Forgot();

        if ($this->request->isPost()) {
            $form->setFieldValues($this->request->getPost(), [
                'strip_tags'   => null,
                'htmlentities' => [ENT_QUOTES, 'UTF-8']
            ]);

            if ($form->isValid()) {
                $user = new Model\User();
                $user->forgot($form->getFields());
                $this->view->success = true;
            } else {
                $this->view->form = $form;
            }
            $this->response->setBody($this->view->render());
            $this->send();
        } else {
            $this->view->form = $form;
            $this->response->setBody($this->view->render());
            $this->send();
        }
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
                $this->view->success = true;
            } else {
                $this->view->form = $form;
            }
            $this->response->setBody($this->view->render());
            $this->send();
        } else {
            $this->view->form = $form;
            $this->response->setBody($this->view->render());
            $this->send();
        }
    }

    public function logout()
    {
        $this->sess->kill();
        Response::redirect(BASE_PATH . APP_URI . '/login');
    }

}