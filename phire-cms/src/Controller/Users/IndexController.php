<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Controller\Users;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Paginator\Paginator;
/**
 * Users Index Controller class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.1.0
 */
class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @param  int $id
     * @return void
     */
    public function index($id = null)
    {
        if ((null === $id) || ($this->services['acl']->isAllowed($this->sess->user->role, 'users-of-role-' . $id, 'index'))) {
            $deniedRoles = [];
            $resources   = $this->services['acl']->getResources();
            foreach ($resources as $name => $resource) {
                if (!$this->services['acl']->isAllowed($this->sess->user->role, $name, 'index')) {
                    $deniedRoles[] = (int)substr($name, strrpos($name, '-') + 1);
                }
            }

            $user = new Model\User();

            $searchAry = null;
            if ((null !== $this->request->getQuery('search_for')) &&
                (null !== $this->request->getQuery('search_by')) &&
                ($this->request->getQuery('search_for') != '') &&
                ($this->request->getQuery('search_by') != '----')) {
                $searchAry = [
                    'for' => $this->request->getQuery('search_for'),
                    'by'  => $this->request->getQuery('search_by')
                ];
            }

            if ($user->hasPages($this->config->pagination, $id, $searchAry, $deniedRoles)) {
                $limit = $this->config->pagination;
                $pages = new Paginator($user->getCount($id, $searchAry, $deniedRoles), $limit);
                $pages->useInput(true);
            } else {
                $limit = null;
                $pages = null;
            }

            $this->prepareView('phire/users/index.phtml');
            $this->view->title       = 'Users';
            $this->view->pages       = $pages;
            $this->view->roleId      = $id;
            $this->view->queryString = $this->getQueryString('sort');
            $this->view->searchFor   = $this->request->getQuery('search_for');
            $this->view->searchBy    = $this->request->getQuery('search_by');
            $this->view->users       = $user->getAll(
                $id, $searchAry, $deniedRoles, $limit,
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
        $this->prepareView('phire/users/add.phtml');
        $this->view->title = 'Add User';

        if ((null !== $rid) && ($this->services['acl']->isAllowed($this->sess->user->role, 'users-of-role-' . $rid, 'add'))) {
            $role = new Model\Role();
            $role->getById($rid);
            $this->view->title .= ' : ' . $role->name;

            if ($role->email_as_username) {
                $fields = $this->application->config()['forms']['Phire\Form\UserEmail'];
            } else {
                $fields = $this->application->config()['forms']['Phire\Form\User'];
                if ($role->email_required) {
                    $fields[2]['email']['required'] = true;
                }
            }

            $fields[1]['password1']['required'] = true;
            $fields[1]['password2']['required'] = true;
            $fields[0]['role_id']['value']      = $rid;

            $this->view->form = ($role->email_as_username) ? new Form\UserEmail($fields) : new Form\User($fields);

            if ($this->request->isPost()) {
                $this->view->form->addFilter('strip_tags', null, 'textarea')
                     ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                     ->setFieldValues($this->request->getPost());

                if ($this->view->form->isValid()) {
                    $this->view->form->clearFilters()
                         ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                         ->filter();
                    $user = new Model\User();
                    $user->save($this->view->form->getFields());

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
            $this->prepareView('phire/users/edit.phtml');
            $this->view->title    = 'Edit User';
            $this->view->username = $user->username;

            $role   = new Model\Role();
            $role->getById($user->role_id);

            if ($role->email_as_username) {
                $fields = $this->application->config()['forms']['Phire\Form\UserEmail'];
                $fields[1]['email']['attributes']['onkeyup'] = 'phire.changeTitle(this.value);';
            } else {
                $fields = $this->application->config()['forms']['Phire\Form\User'];
                $fields[1]['username']['attributes']['onkeyup'] = 'phire.changeTitle(this.value);';
                if ($role->email_required) {
                    $fields[2]['email']['required'] = true;
                }
            }

            $roles      = $role->getAll();
            $roleValues = [];
            foreach ($roles as $r) {
                $roleValues[$r->id] = $r->name;
            }

            $fields[1]['password1']['required'] = false;
            $fields[1]['password2']['required'] = false;
            $fields[0]['role_id']['type']       = 'select';
            $fields[0]['role_id']['label']      = 'Role';
            $fields[0]['role_id']['value']      = $roleValues;
            $fields[0]['role_id']['marked']     = $user->role_id;
            $fields[0]['role_id']['attributes'] = [
                'onchange' => 'phire.checkUserRole(this);'
            ];

            $this->view->form = ($role->email_as_username) ? new Form\UserEmail($fields) : new Form\User($fields);
            $this->view->form->addFilter('strip_tags', null, 'textarea')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($user->toArray());

            if ($this->request->isPost()) {
                $this->view->form->addFilter('strip_tags', null, 'textarea')
                     ->setFieldValues($this->request->getPost());

                if ($this->view->form->isValid()) {
                    $this->view->form->clearFilters()
                         ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                         ->filter();
                    $user = new Model\User();
                    $user->update($this->view->form->getFields(), $this->sess);

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
     * Remove action method
     *
     * @return void
     */
    public function process()
    {
        if ($this->request->isPost()) {
            $user = new Model\User();
            $user->process($this->request->getPost());
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