<?php

namespace Phire\Controller;

use Phire\Form;
use Phire\Model;
use Pop\Http\Response;

class UsersController extends AbstractController
{

    public function index()
    {
        $user = new Model\User();
        $this->prepareView('users/index.phtml');
        $this->view->title = 'Users';
        $this->view->users = $user->getAll();
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function add()
    {
        $this->prepareView('users/add.phtml');
        $this->view->title = 'Add User';

        $form = new Form\User();

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

                Response::redirect(BASE_PATH . APP_URI . '/users');
                exit();
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function edit($id)
    {
        $user = new Model\User();
        $user->getById($id);

        $this->prepareView('users/edit.phtml');
        $this->view->title    = 'Edit User';
        $this->view->username = $user->username;

        $form = new Form\User();
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
                $user->update($form->getFields());

                Response::redirect(BASE_PATH . APP_URI . '/users');
                exit();
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function remove()
    {
        if ($this->request->isPost()) {
            $user = new Model\User();
            $user->remove($this->request->getPost());
        }
        Response::redirect(BASE_PATH . APP_URI . '/users');
    }

}