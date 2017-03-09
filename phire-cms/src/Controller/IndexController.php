<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Controller;

use Phire\Form;
use Phire\Model;
use Pop\Auth;

/**
 * Index controller class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
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
        $this->prepareView('index.phtml');
        $this->view->title     = 'Dashboard';
        $this->view->dbVersion = $this->services['database']->getVersion();
        $this->view->database  = (strtolower($this->application->config()['database']['adapter']) == 'pdo') ?
            $this->application->config()['database']['type'] . ' (pdo)' :
            $this->view->database = $this->application->config()['database']['adapter'];

        $this->send();
    }

    /**
     * Static side nav example action method
     *
     * @return void
     */
    public function side()
    {
        $this->prepareView('index-side.phtml');

        $this->view->sideNav   = $this->services['nav.side'];
        $this->view->title     = 'Dashboard';
        $this->view->dbVersion = $this->services['database']->getVersion();
        $this->view->database  = (strtolower($this->application->config()['database']['adapter']) == 'pdo') ?
            $this->application->config()['database']['type'] . ' (pdo)' :
            $this->view->database = $this->application->config()['database']['adapter'];

        $this->send();
    }

    /**
     * Profile action method
     *
     * @return void
     */
    public function profile()
    {
        $this->prepareView('profile.phtml');
        $this->view->title = 'My Profile';

        $user = new Model\User();
        $user->getById($this->sess->user->id);

        $this->view->form = Form\Profile::createFromFieldsetConfig($this->application->config()['forms']['Phire\Form\Profile']);
        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($user->toArray());

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filterValues();

                $user = new Model\User();
                $user->update(
                    $this->view->form,
                    $this->application->config()['application_title'],
                    $this->application->services()->get('mailer'),
                    $this->sess
                );
                $this->view->id = $user->id;
                $this->sess->setRequestValue('saved', true);
                $this->redirect(BASE_PATH . APP_URI . '/profile');
            }
        }

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
        $this->view->form  = Form\Login::createFromFieldsetConfig($this->application->config()['forms']['Phire\Form\Login']);

        if ($this->request->isPost()) {
            $auth = new Auth\Table('Phire\Table\Users');

            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost(), $auth);

            $user = new Model\User();

            if ($this->view->form->isValid()) {
                $user->login($auth->getUser(), $this->sess);
                $this->redirect(BASE_PATH . APP_URI . '/');
            } else {
                if ((null !== $auth->getUser()) && (null !== $auth->getUser()->id)) {
                    if ($this->view->form->isValid()) {
                        $this->sess->setRequestValue('failed', true);
                        $this->redirect(BASE_PATH . APP_URI . '/login');
                    }
                }
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
        if (isset($this->sess->user)) {
            $user = new Model\User();
            $user->logout($this->sess);
        }

        if ((int)$this->request->getQuery('expired') == 1) {
            $this->sess->setRequestValue('expired', true);
        }

        $this->redirect(BASE_PATH . APP_URI . '/login');
    }

    /**
     * Forgot action method
     *
     * @return void
     */
    public function forgot()
    {
        $this->prepareView('forgot.phtml');
        $this->view->title   = 'Password Reset';
        $this->view->success = false;
        $this->view->form    = Form\Forgot::createFromFieldsetConfig($this->application->config()['forms']['Phire\Form\Forgot']);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filterValues();

                $user = new Model\User();
                $user->forgot(
                    $this->view->form,
                    $this->application->config()['application_title'],
                    $this->application->services()->get('mailer')
                );
                $this->view->id      = $user->id;
                $this->view->success = true;
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
        $this->prepareView('verify.phtml');
        $this->view->title = 'Verify Your Email';

        $user = new Model\User();
        $this->view->result = $user->verify($id, $hash);
        $this->view->id     = $user->id;

        $this->send();
    }

}