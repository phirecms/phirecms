<?php

namespace Phire\Controller\Users;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
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

        $fields = $this->application->config()['forms']['Phire\Form\UserRole'];
        $config = $this->application->config();

        $resources = ['----' => '----'];
        $parents   = ['----' => '----'];
        $roles     = (new Model\UserRole())->getAll();
        if (count($roles) > 0) {
            foreach ($roles as $role) {
                $parents[$role['id']] = $role['name'];
            }
        }

        foreach ($config['resources'] as $resource => $perms) {
            $resources[$resource] = $resource;
        }

        $fields[0]['parent_id']['value']  = $parents;
        $fields[2]['resource_1']['value'] = $resources;

        $this->view->form = new Form\UserRole($fields);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $role->save($this->request->getPost());
                $this->view->id = $role->id;
                $this->redirect(BASE_PATH . APP_URI . '/users/roles/edit/' . $role->id . '?saved=' . time());
            }
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
        $role = new Model\UserRole();
        $role->getById($id);

        if (!isset($role->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/users/roles');
        }

        $this->prepareView('users/roles/edit.phtml');
        $this->view->title     = 'Users : Edit Role';
        $this->view->role_name = $role->name;

        $fields = $this->application->config()['forms']['Phire\Form\UserRole'];
        $config = $this->application->config();

        $resources = ['----' => '----'];
        $parents   = ['----' => '----'];
        $roles     = (new Model\UserRole())->getAll();
        if (count($roles) > 0) {
            foreach ($roles as $r) {
                if ($r['id'] != $id) {
                    $parents[$r['id']] = $r['name'];
                }
            }
        }

        foreach ($config['resources'] as $resource => $perms) {
            $resources[$resource] = $resource;
        }

        $fields[0]['parent_id']['value']  = $parents;
        $fields[2]['resource_1']['value'] = $resources;

        $this->view->form = new Form\UserRole($fields);
        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($role->toArray());

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $role = new Model\UserRole();
                $role->update($this->request->getPost());
                $this->view->id = $role->id;
                $this->redirect(BASE_PATH . APP_URI . '/users/roles/edit/' . $role->id . '?saved=' . time());
            }
        }

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
            $config = $this->application->config();
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
        $this->redirect(BASE_PATH . APP_URI . '/users/roles?removed=' . time());
    }

}