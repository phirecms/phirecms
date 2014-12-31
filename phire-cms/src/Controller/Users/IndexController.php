<?php

namespace Phire\Controller\Users;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Http\Response;
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
        if ((null === $id) || ($this->services['acl']->isAllowed($this->sess->user->role, 'user-role-' . $id, 'index'))) {
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
            Response::redirect(BASE_PATH . APP_URI . '/users');
            exit();
        }
    }

    /**
     * Add action method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView('users/add.phtml');
        $this->view->title = 'Add User';

        $form = new Form\User($this->services['acl'], $this->sess->user);

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();
                $user = new Model\User();
                $user->save($form->getFields());

                Response::redirect(BASE_PATH . APP_URI . '/users/edit/' . $user->id . '?saved=' . time());
                exit();
            }
        }

        $this->view->form = $form;
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

        if ($this->services['acl']->isAllowed($this->sess->user->role, 'user-role-' . $user->role_id, 'edit')) {
            $this->prepareView('users/edit.phtml');
            $this->view->title    = 'Edit User';
            $this->view->username = $user->username;

            $form = new Form\User($this->services['acl'], $this->sess->user);
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
                    $user = new Model\User();
                    $user->update($form->getFields(), $this->sess);

                    Response::redirect(BASE_PATH . APP_URI . '/users/edit/' . $user->id . '?saved=' . time());
                    exit();
                }
            }

            $this->view->form = $form;
            $this->send();
        } else {
            Response::redirect(BASE_PATH . APP_URI . '/users');
            exit();
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
        Response::redirect(BASE_PATH . APP_URI . '/users?removed=' . time());
    }

}