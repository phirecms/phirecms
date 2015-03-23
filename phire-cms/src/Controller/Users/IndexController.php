<?php

namespace Phire\Controller\Users;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Paginator\Paginator;

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

            if ($user->hasPages($this->config->pagination, $id, $this->request->getQuery('username'), $deniedRoles)) {
                $limit = $this->config->pagination;
                $pages = new Paginator($user->getCount($id, $this->request->getQuery('username'), $deniedRoles), $limit);
                $pages->useInput(true);
            } else {
                $limit = null;
                $pages = null;
            }

            $this->prepareView('users/index.phtml');
            $this->view->title    = 'Users';
            $this->view->pages    = $pages;
            $this->view->roleId   = $id;
            $this->view->username = $this->request->getQuery('username');
            $this->view->users    = $user->getAll(
                $id, $this->request->getQuery('username'), $deniedRoles, $limit,
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

        if ((null !== $rid) && ($this->services['acl']->isAllowed($this->sess->user->role, 'users-of-role-' . $rid, 'add'))) {
            $role = new Model\Role();
            $role->getById($rid);
            $this->view->title .= ' : ' . $role->name;

            $fields = $this->application->config()['forms']['Phire\Form\User'];

            if ($role->email_as_username) {
                unset($fields[1]['username']);
            }

            $fields[0]['role_id']['value'] = $rid;

            $this->view->form = new Form\User($fields);

            if ($this->request->isPost()) {
                $this->view->form->addFilter('strip_tags')
                    ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                    ->setFieldValues($this->request->getPost());

                if ($this->view->form->isValid()) {
                    $this->view->form->clearFilters()
                        ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                        ->filter();
                    $user = new Model\User();
                    $user->save($this->view->form->getFields());

                    $this->view->id = $user->id;
                    $this->redirect(BASE_PATH . APP_URI . '/users/edit/' . $user->id . '?saved=' . time());
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
            $this->view->title    = 'Edit User';
            $this->view->username = $user->username;

            $fields = $this->application->config()['forms']['Phire\Form\User'];
            $role   = new Model\Role();
            $role->getById($user->role_id);



            if ($role->email_as_username) {
                unset($fields[1]['username']);
                $fields[1]['email1']['attributes']['onkeyup'] = 'phire.changeTitle(this.value);';
            } else {
                $fields[1]['username']['attributes']['onkeyup'] = 'phire.changeTitle(this.value);';
            }

            $fields[0]['role_id']['value'] = $user->role_id;

            $this->view->form = new Form\User($fields);
            $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($user->toArray());

            if ($this->request->isPost()) {
                $this->view->form->addFilter('strip_tags')
                     ->setFieldValues($this->request->getPost());

                if ($this->view->form->isValid()) {
                    $this->view->form->clearFilters()
                         ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                         ->filter();
                    $user = new Model\User();
                    $user->update($this->view->form->getFields(), $this->sess);

                    $this->view->id = $user->id;
                    $this->redirect(BASE_PATH . APP_URI . '/users/edit/' . $user->id . '?saved=' . time());
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
    public function remove()
    {
        if ($this->request->isPost()) {
            $user = new Model\User();
            $user->remove($this->request->getPost());
        }
        $this->redirect(BASE_PATH . APP_URI . '/users?removed=' . time());
    }

}