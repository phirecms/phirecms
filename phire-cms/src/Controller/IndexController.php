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
        $config = new Model\Config();

        $this->prepareView('index.phtml');
        $this->view->title    = 'Dashboard';
        $this->view->overview = $config->overview;
        $this->view->config   = $config->config;
        $this->view->modules  = $config->modules;
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
        $this->view->title = 'Login';

        $form = new Form\Login($this->application->config()['forms']['Phire\Form\Login']);

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
                    'id'       => $auth->adapter()->getUser()->id,
                    'role_id'  => $auth->adapter()->getUser()->role_id,
                    'role'     => Table\UserRoles::findById($auth->adapter()->getUser()->role_id)->name,
                    'username' => $auth->adapter()->getUser()->username,
                    'email'    => $auth->adapter()->getUser()->email,
                ], \ArrayObject::ARRAY_AS_PROPS);

                $this->application->trigger('app.send', ['controller' => $this]);
                Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
                exit();
            }
        }

        $this->view->form = $form;
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

            $captcha = (isset($this->application->config()['registration_captcha']) &&
                ($this->application->config()['registration_captcha']));

            $csrf = (isset($this->application->config()['registration_csrf']) &&
                ($this->application->config()['registration_csrf']));

            $form = new Form\Register($id, $captcha, $csrf, $this->application->config()['forms']['Phire\Form\Register']);

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
                $this->send();
            } else {
                $this->view->form = $form;
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

        $form = new Form\Profile($this->sess->user->role_id, $this->application->config()['forms']['Phire\Form\Profile']);

        $form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($user->toArray());

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
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
        $this->view->title = 'Password Reminder';

        $form = new Form\Forgot($this->application->config()['forms']['Phire\Form\Forgot']);

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
            $this->send();
        } else {
            $this->view->form = $form;
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

        $form = new Form\Unsubscribe($this->application->config()['forms']['Phire\Form\Unsubscribe']);

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
                $this->sess->kill();
                Response::redirect(BASE_PATH . APP_URI . '/unsubscribe?success=1');
                exit();
            } else {
                $this->view->form = $form;
            }
            $this->send();
        } else {
            $this->view->form = $form;
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