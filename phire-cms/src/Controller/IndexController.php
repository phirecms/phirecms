<?php

namespace Phire\Controller;

use Phire\Form;
use Phire\Model;
use Phire\Table;
use Pop\Auth;
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
        $this->prepareView('index.phtml');
        $this->view->title = 'Dashboard';
        $this->response->setBody($this->view->render());
        $this->send();
    }

    /**
     * Login action method
     *
     * @return void
     */
    public function login()
    {
        $this->prepareView('login.phtml');
        $this->view->title = 'Please Login';

        $form = new Form\Login();

        if ($this->request->isPost()) {
            $auth = new Auth\Auth(
                new Auth\Adapter\Table(
                    'Phire\Table\Users',
                    Auth\Auth::ENCRYPT_BCRYPT
                )
            );

            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost(), $auth);

            if ($form->isValid()) {
                $this->sess->user = new \ArrayObject([
                    'id'        => $auth->adapter()->getUser()->id,
                    'role_id'   => $auth->adapter()->getUser()->role_id,
                    'role_name' => Table\UserRoles::findById($auth->adapter()->getUser()->role_id)->name,
                    'username'  => $auth->adapter()->getUser()->username,
                    'email'     => $auth->adapter()->getUser()->email,
                ], \ArrayObject::ARRAY_AS_PROPS);

                Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
                exit();
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    /**
     * Register action method
     *
     * @param  int $id
     * @return void
     */
    public function register($id)
    {
        $role = new Model\UserRole();

        if ($role->canRegister($id)) {
            $this->prepareView('register.phtml');
            $this->view->title = 'Register';

            $form = new Form\Register($id);

            if ($this->request->isPost()) {
                $form->addFilter('strip_tags')
                     ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                     ->setFieldValues($this->request->getPost());

                if ($form->isValid()) {
                    $form->clearFilters()
                         ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                         ->filter();

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
            Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
        }
    }

    /**
     * Profile action method
     *
     * @return void
     */
    public function profile()
    {
        $this->prepareView('profile.phtml');
        $this->view->title = 'Profile';

        $user = new Model\User();
        $user->getById($this->sess->user->id);

        $form = new Form\Profile($this->sess->user->role_id);

        $form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($user->toArray());

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $fields = $form->getFields();
                $role   = new Model\UserRole();
                $role->getById($this->sess->user->role_id);
                $fields['verified'] = (int)!($role->verification);

                $user = new Model\User();
                $user->update($fields, $this->sess);
                Response::redirect(BASE_PATH . APP_URI . '/profile?saved=' . time());
                exit();
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    /**
     * Verify action method
     *
     * @param  int    $id
     * @param  string $hash
     * @return void
     */
    public function verify($id, $hash)
    {
        $user = new Model\User();
        $this->prepareView('verify.phtml');
        $this->view->title  = 'Verify Your Email';
        $this->view->result = $user->verify($id, $hash);
        $this->response->setBody($this->view->render());
        $this->send();
    }

    /**
     * Forgot action method
     *
     * @return void
     */
    public function forgot()
    {
        $this->prepareView('forgot.phtml');
        $this->view->title = 'Forgot Your Password?';

        $form = new Form\Forgot();

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

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

    /**
     * Unsubscribe action method
     *
     * @return void
     */
    public function unsubscribe()
    {
        $this->prepareView('unsubscribe.phtml');
        $this->view->title = 'Unsubscribe';

        $form = new Form\Unsubscribe();

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

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

    /**
     * Logout action method
     *
     * @return void
     */
    public function logout()
    {
        $this->sess->kill();
        Response::redirect(BASE_PATH . APP_URI . '/login');
    }

}