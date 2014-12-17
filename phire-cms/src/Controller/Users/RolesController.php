<?php

namespace Phire\Controller\Users;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Http\Response;

class RolesController extends AbstractController
{

    public function index()
    {
        $role = new Model\UserRole();
        $this->prepareView('users/roles/index.phtml');
        $this->view->title = 'Roles';
        $this->view->roles = $role->getAll();
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function add()
    {
        $this->prepareView('users/roles/add.phtml');
        $this->view->title = 'Add Role';

        $form = new Form\UserRole();

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $role = new Model\Role();
                $role->save($this->request->getPost());

                Response::redirect(BASE_PATH . APP_URI . '/users/roles');
                exit();
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function edit($id)
    {
        $role = new Model\UserRole();
        $role->getById($id);

        $this->prepareView('users/roles/edit.phtml');
        $this->view->title     = 'Edit Role';
        $this->view->role_name = $role->name;

        $form = new Form\UserRole($role->permissions, $id);
        $form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($role->toArray());

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $role = new Model\UserRole();
                $role->update($this->request->getPost());

                Response::redirect(BASE_PATH . APP_URI . '/users/roles');
                exit();
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function json($id)
    {
        $role = new Model\UserRole();
        $role->getById($id);
        $json = [];

        if (isset($role->id)) {
            $json['id']                = $role->id;
            $json['verification']      = $role->verification;
            $json['approval']          = $role->approval;
            $json['email_as_username'] = $role->email_as_username;
        }

        $this->response->setBody(json_encode($json, JSON_PRETTY_PRINT));
        $this->send(200, ['Content-Type' => 'application/json']);
    }

    public function remove()
    {
        if ($this->request->isPost()) {
            $role = new Model\UserRole();
            $role->remove($this->request->getPost());
        }
        Response::redirect(BASE_PATH . APP_URI . '/users/roles');
    }

}