<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Controller;

use Phire\Form;
use Phire\Model;
use Phire\Table;
use Pop\Auth;
use Pop\Web\Cookie;

/**
 * Index Controller class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.2
 */
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

        if (!isset($this->sess->updates)) {
            $this->sess->updates = $config->getUpdates($this->application->config()['updates']);
        }

        $this->prepareView('phire/index.phtml');
        $this->view->title                = 'Dashboard';
        $this->view->overview             = $config->overview;
        $this->view->config               = $config->config;
        $this->view->modules              = $config->modules;
        $this->view->phire_update_version = $this->sess->updates->phirecms;

        $this->send();
    }

    /**
     * Login action method
     *
     * @return void
     */
    public function login()
    {
        $this->prepareView('phire/login.phtml');
        $this->view->title = 'Login';
        $this->view->form  = new Form\Login($this->application->config()['forms']['Phire\Form\Login']);;

        if ($this->request->isPost()) {
            $auth = new Auth\Auth(
                new Auth\Adapter\Table(
                    'Phire\Table\Users',
                    Auth\Auth::ENCRYPT_BCRYPT
                )
            );

            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost(), $auth);

            if ($this->view->form->isValid()) {
                $this->sess->user = new \ArrayObject([
                    'id'       => $auth->adapter()->getUser()->id,
                    'role_id'  => $auth->adapter()->getUser()->role_id,
                    'role'     => Table\Roles::findById($auth->adapter()->getUser()->role_id)->name,
                    'username' => $auth->adapter()->getUser()->username,
                    'email'    => $auth->adapter()->getUser()->email,
                ], \ArrayObject::ARRAY_AS_PROPS);

                if (php_sapi_name() != 'cli') {
                    $path = BASE_PATH . APP_URI;
                    if ($path == '') {
                        $path = '/';
                    }

                    $cookie = Cookie::getInstance(['path' => $path]);
                    $cookie->set('phire', [
                        'base_path'    => BASE_PATH,
                        'app_path'     => APP_PATH,
                        'content_path' => CONTENT_PATH,
                        'modules_path' => MODULES_PATH,
                        'app_uri'      => APP_URI
                    ]);
                }

                $this->redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
            }
        }

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
        $role = new Model\Role();

        if ($role->canRegister($id)) {
            $this->prepareView('phire/register.phtml');
            $this->view->title = 'Register';

            $captcha = (isset($this->application->config()['registration_captcha']) &&
                ($this->application->config()['registration_captcha']));

            $csrf = (isset($this->application->config()['registration_csrf']) &&
                ($this->application->config()['registration_csrf']));

            $role->getById($id);

            if ($role->email_as_username) {
                $fields = $this->application->config()['forms']['Phire\Form\RegisterEmail'];
                $fields[2]['role_id']['value'] = $id;
                $this->view->form = new Form\RegisterEmail($captcha, $csrf, $fields);
            } else {
                $fields = $this->application->config()['forms']['Phire\Form\Register'];
                $fields[2]['role_id']['value'] = $id;
                if ($role->email_required) {
                    $fields[1]['email']['required'] = true;
                }
                $this->view->form = new Form\Register($captcha, $csrf, $fields);
            }

            if ($this->request->isPost()) {
                $this->view->form->addFilter('strip_tags')
                     ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                     ->setFieldValues($this->request->getPost());

                if ($this->view->form->isValid()) {
                    $this->view->form->clearFilters()
                         ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                         ->filter();

                    $fields = $this->view->form->getFields();
                    $role->getById($id);
                    $fields['active']   = (int)!($role->approval);
                    $fields['verified'] = (int)!($role->verification);

                    $user = new Model\User();
                    $user->save($fields);

                    $this->view->id       = $user->id;
                    $this->view->success  = true;
                    $this->view->verified = $user->verified;
                }
            }
            $this->send();
        } else {
            $this->redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
        }
    }

    /**
     * Profile action method
     *
     * @return void
     */
    public function profile()
    {
        $this->prepareView('phire/profile.phtml');
        $this->view->title = 'Profile';

        $user = new Model\User();
        $user->getById($this->sess->user->id);

        $role = new Model\Role();
        $role->getById($this->sess->user->role_id);

        if ($role->email_as_username) {
            $fields = $this->application->config()['forms']['Phire\Form\ProfileEmail'];
            $fields[2]['role_id']['value'] = $this->sess->user->role_id;
            $this->view->form = new Form\ProfileEmail($fields);
        } else {
            $fields = $this->application->config()['forms']['Phire\Form\Profile'];
            $fields[2]['role_id']['value'] = $this->sess->user->role_id;
            if ($role->email_required) {
                $fields[1]['email']['required'] = true;
            }
            $this->view->form = new Form\Profile($fields);
        }

        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($user->toArray());

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $fields = $this->view->form->getFields();
                $role   = new Model\Role();
                $role->getById($this->sess->user->role_id);
                $fields['verified'] = (int)!($role->verification);

                $user = new Model\User();
                $user->update($fields, $this->sess);
                $this->view->id = $user->id;
                $this->sess->setRequestValue('saved', true);
                $this->redirect(BASE_PATH . APP_URI . '/profile');
            }
        }

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
        $this->prepareView('phire/verify.phtml');
        $this->view->title  = 'Verify Your Email';
        $this->view->result = $user->verify($id, $hash);
        $this->view->id = $user->id;
        $this->send();
    }

    /**
     * Forgot action method
     *
     * @return void
     */
    public function forgot()
    {
        $this->prepareView('phire/forgot.phtml');
        $this->view->title = 'Password Reminder';

        $this->view->form = new Form\Forgot($this->application->config()['forms']['Phire\Form\Forgot']);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $user = new Model\User();
                $user->forgot($this->view->form->getFields());
                unset($this->view->form);
                $this->view->id      = $user->id;
                $this->view->success = true;
            }
        }

        $this->send();
    }

    /**
     * Unsubscribe action method
     *
     * @return void
     */
    public function unsubscribe()
    {
        $this->prepareView('phire/unsubscribe.phtml');
        $this->view->title = 'Unsubscribe';

        $this->view->form = new Form\Unsubscribe($this->application->config()['forms']['Phire\Form\Unsubscribe']);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $user = new Model\User();
                $user->unsubscribe($this->view->form->getFields());
                $this->view->success = true;
                $this->view->id      = $user->id;
                $this->sess->kill();
                $this->redirect(BASE_PATH . APP_URI . '/unsubscribe?success=1');
            }
        }

        $this->send();
    }

    /**
     * Logout action method
     *
     * @return void
     */
    public function logout()
    {
        $this->sess->kill();

        $path = BASE_PATH . APP_URI;
        if ($path == '') {
            $path = '/';
        }

        $cookie = Cookie::getInstance(['path' => $path]);
        $cookie->delete('phire');

        $this->redirect(BASE_PATH . APP_URI . '/login');
    }

}