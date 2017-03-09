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
namespace Phire\Controller\Users;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Paginator\Form as Paginator;

/**
 * Users controller class
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
     * @param  int $rid
     * @return void
     */
    public function index($rid = null)
    {
        if ((null === $rid) || ($this->services['acl']->isAllowed($this->sess->user->role, 'users-of-role-' . $rid, 'index'))) {
            $deniedRoles = [];
            $resources   = $this->services['acl']->getResources();
            foreach ($resources as $name => $resource) {
                if (!$this->services['acl']->isAllowed($this->sess->user->role, $name, 'index')) {
                    $deniedRoles[] = (int)substr($name, strrpos($name, '-') + 1);
                }
            }

            $user = new Model\User();

            $searchUsername = $this->request->getQuery('search_username');

            if ($user->hasPages($this->application->config()['pagination'], $rid, $searchUsername, $deniedRoles)) {
                $limit = $this->application->config()['pagination'];
                $pages = new Paginator($user->getCount($rid, $searchUsername, $deniedRoles), $limit);
            } else {
                $limit = null;
                $pages = null;
            }

            $this->prepareView('users/index.phtml');
            $this->view->title          = 'Users';
            $this->view->pages          = $pages;
            $this->view->roleId         = $rid;
            $this->view->queryString    = $this->getQueryString('sort');
            $this->view->searchUsername = $searchUsername;
            $this->view->users          = $user->getAll(
                $rid, $searchUsername, $deniedRoles, $limit,
                $this->request->getQuery('page'), $this->request->getQuery('sort')
            );
            $this->view->roles = $user->getRoles();
            $this->send();
        } else {
            $this->redirect(BASE_PATH . APP_URI . '/users');
        }
    }

    /**
     * Add action method
     *
     * @param  int $rid
     * @return void
     */
    public function add($rid = null)
    {
        $this->prepareView('users/add.phtml');
        $this->view->title = 'Add User';

        if (null !== $rid) {
            $role = new Model\Role();
            $role->getById($rid);
            $this->view->title .= ' : ' . $role->name;

            $fields = $this->application->config()['forms']['Phire\Form\User'];
            $fields[1]['password1']['required']   = true;
            $fields[1]['password1']['validators'] = new \Pop\Validator\LengthGte(6);
            $fields[1]['password2']['required']   = true;
            $fields[0]['role_id']['value']        = $rid;

            $this->view->form = Form\User::createFromFieldsetConfig($fields);
            if ($this->request->isPost()) {
                $this->view->form->addFilter('strip_tags')
                     ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                     ->setFieldValues($this->request->getPost());

                if ($this->view->form->isValid()) {
                    $this->view->form->clearFilters()
                         ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                         ->filterValues();
                    $user = new Model\User();
                    $user->save(
                        $this->view->form,
                        $this->application->config()['application_title'],
                        $this->application->services()->get('mailer')
                    );

                    $this->view->id = $user->id;
                    $this->sess->setRequestValue('saved', true);
                    $this->redirect(BASE_PATH . APP_URI . '/users/edit/' . $user->id);
                }
            }
        } else {
            $this->view->roles = (new Model\Role())->getAll();
        }

        $this->send();
    }

    /**
     * Edit action method
     *
     * @param  int $id
     * @return void
     */
    public function edit($id)
    {
        $user = new Model\User();
        $user->getById($id);

        if (!isset($user->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/users');
        }

        if ($this->services['acl']->isAllowed($this->sess->user->role, 'users-of-role-' . $user->role_id, 'edit')) {
            $this->prepareView('users/edit.phtml');
            $this->view->title      = 'Edit User';
            $this->view->username   = $user->username;

            $role       = new Model\Role();
            $roles      = $role->getAll();
            $roleValues = [];
            foreach ($roles as $r) {
                $roleValues[$r->id] = $r->name;
            }

            $fields = $this->application->config()['forms']['Phire\Form\User'];

            $fields[1]['username']['attributes']['onkeyup'] = 'phire.changeTitle(this.value);';
            $fields[1]['password1']['required']    = false;
            $fields[1]['password2']['required']    = false;
            $fields[0]['role_id']['type']          = 'select';
            $fields[0]['role_id']['label']         = 'Role';
            $fields[0]['role_id']['attributes']    = ['class' => 'phire-select'];
            $fields[0]['role_id']['values']        = $roleValues;
            $fields[0]['role_id']['selected']      = $user->role_id;

            $this->view->form = Form\User::createFromFieldsetConfig($fields);
            $this->view->form->addFilter('strip_tags', null, 'textarea')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($user->toArray());

            if ($this->request->isPost()) {
                $this->view->form->addFilter('strip_tags', null, 'textarea')
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
                    $this->redirect(BASE_PATH . APP_URI . '/users/edit/' . $user->id);
                }
            }
            $this->send();
        } else {
            $this->redirect(BASE_PATH . APP_URI . '/users');
        }
    }

    /**
     * Process action method
     *
     * @return void
     */
    public function process()
    {
        if ($this->request->isPost()) {
            $user = new Model\User();
            $user->process(
                $this->request->getPost(),
                $this->application->config()['application_title'],
                $this->application->services()->get('mailer')
            );
        }

        if ((null !== $this->request->getPost('user_process_action')) && ($this->request->getPost('user_process_action') == -1)) {
            $this->sess->setRequestValue('removed', true);
        } else {
            $this->sess->setRequestValue('saved', true);
        }

        $this->redirect(BASE_PATH . APP_URI . '/users' .
            (((int)$this->request->getPost('role_id') != 0) ? '/' . (int)$this->request->getPost('role_id') : null));
    }

}