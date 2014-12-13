<?php

namespace Phire\Controller;

use Phire\Form;
use Pop\Auth;
use Pop\Http\Response;
use Pop\View\View;

class IndexController extends AbstractController
{

    public function index()
    {
        if (!isset($this->sess->user)) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
        } else {
            $view = new View($this->viewPath . '/index.phtml');
            $view->title    = 'Welcome to Phire';
            $view->username = $this->sess->user['username'];

            $this->response->setBody($view->render());
            $this->send();
        }
    }

    public function login()
    {
        if (isset($this->sess->user)) {
            Response::redirect(BASE_PATH . APP_URI);
        } else {
            $view        = new View($this->viewPath . '/login.phtml');
            $form        = new Form\Login();
            $view->title = 'Login';

            if ($this->request->isPost()) {
                $auth = new Auth\Auth(
                    new Auth\Adapter\Table(
                        'Phire\Table\Users',
                        (int)\Phire\Table\Config::findById('password_encryption')->value
                    )
                );
                $form->setFieldValues($this->request->getPost(), [
                    'strip_tags'         => null,
                    'html_entity_decode' => [ENT_QUOTES, 'UTF-8']
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

            $view->form = $form;
            $this->response->setBody($view->render());
            $this->send();
        }
    }

    public function logout()
    {
        $this->sess->kill();
        Response::redirect(BASE_PATH . APP_URI . '/login');
    }

    public function error()
    {
        $view = new View($this->viewPath . '/error.phtml');
        $view->title = 'Error';

        $this->response->setBody($view->render());
        $this->send(404);
    }

}