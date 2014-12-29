<?php

namespace Phire\Controller\Users;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Http\Response;
use Pop\Paginator\Paginator;

class RolesController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $role = new Model\UserRole();

        if ($role->hasPages($this->config->pagination)) {
            $limit = $this->config->pagination;
            $pages = new Paginator($role->getCount(), $limit);
            $pages->useInput(true);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->prepareView('users/roles/index.phtml');
        $this->view->title = 'Users : Roles';
        $this->view->pages = $pages;
        $this->view->roles = $role->getAll($limit, $this->request->getQuery('page'), $this->request->getQuery('sort'));
        $this->send();
    }

    /**
     * Add action method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView('users/roles/add.phtml');
        $this->view->title = 'Users : Add Role';
        $role = new Model\UserRole();

        $config = $this->application->config();
        $form   = new Form\UserRole($config['resources']);

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {

                $role->save($this->request->getPost());

                Response::redirect(BASE_PATH . APP_URI . '/users/roles/edit/' . $role->id . '?saved=' . time());
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
        $role = new Model\UserRole();
        $role->getById($id);

        $this->prepareView('users/roles/edit.phtml');
        $this->view->title     = 'Users : Edit Role';
        $this->view->role_name = $role->name;

        $config = $this->application->config();
        $form   = new Form\UserRole($config['resources'], $role->permissions, $id);
        $form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($role->toArray());

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $role = new Model\UserRole();
                $role->update($this->request->getPost());

                Response::redirect(BASE_PATH . APP_URI . '/users/roles/edit/' . $role->id . '?saved=' . time());
                exit();
            }
        }

        $this->view->form = $form;
        $this->send();
    }

    /**
     * JSON action method
     *
     * @param  int $id
     * @return void
     */
    public function json($id)
    {
        $json = [];

        if (is_numeric($id)) {
            $role = new Model\UserRole();
            $role->getById($id);

            if (isset($role->id)) {
                $json['id'] = $role->id;
                $json['verification'] = $role->verification;
                $json['approval'] = $role->approval;
                $json['email_as_username'] = $role->email_as_username;
            }
        } else {
            $config    = $this->application->config();
            if (isset($config['resources'][$id])) {
                $json['permissions'] = $config['resources'][$id];
            }
        }

        $this->response->setBody(json_encode($json, JSON_PRETTY_PRINT));
        $this->send(200, ['Content-Type' => 'application/json']);
    }

    /**
     * Remove action method
     *
     * @return void
     */
    public function remove()
    {
        if ($this->request->isPost()) {
            $role = new Model\UserRole();
            $role->remove($this->request->getPost());
        }
        Response::redirect(BASE_PATH . APP_URI . '/users/roles?removed=' . time());
    }

}