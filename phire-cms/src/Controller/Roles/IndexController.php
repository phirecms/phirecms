<?php

namespace Phire\Controller\Roles;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Pop\Paginator\Paginator;

class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        $role = new Model\Role();

        if ($role->hasPages($this->config->pagination)) {
            $limit = $this->config->pagination;
            $pages = new Paginator($role->getCount(), $limit);
            $pages->useInput(true);
        } else {
            $limit = null;
            $pages = null;
        }

        $this->prepareView('roles/index.phtml');
        $this->view->title = 'Roles';
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
        $this->prepareView('roles/add.phtml');
        $this->view->title = 'Roles : Add';
        $role = new Model\Role();

        $fields = $this->application->config()['forms']['Phire\Form\Role'];
        $config = $this->application->config();

        $resources = ['----' => '----'];
        $parents   = ['----' => '----'];
        $roles     = (new Model\Role())->getAll();
        if (count($roles) > 0) {
            foreach ($roles as $r) {
                $parents[$r['id']] = $r['name'];
            }
        }

        foreach ($config['resources'] as $resource => $perms) {
            $resources[$resource] = $resource;
        }

        $fields[0]['parent_id']['value']  = $parents;
        $fields[2]['resource_1']['value'] = $resources;

        $this->view->form = new Form\Role($fields);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $role->save($this->request->getPost());
                $this->view->id = $role->id;
                $this->redirect(BASE_PATH . APP_URI . '/roles/edit/' . $role->id . '?saved=' . time());
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
        $role = new Model\Role();
        $role->getById($id);

        if (!isset($role->id)) {
            $this->redirect(BASE_PATH . APP_URI . '/roles');
        }

        $this->prepareView('roles/edit.phtml');
        $this->view->title     = 'Roles';
        $this->view->role_name = $role->name;

        $fields = $this->application->config()['forms']['Phire\Form\Role'];
        $config = $this->application->config();

        $resources = ['----' => '----'];
        $parents   = ['----' => '----'];
        $roles     = (new Model\Role())->getAll();
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

        $this->view->form = new Form\Role($fields);
        $this->view->form->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
             ->setFieldValues($role->toArray());

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $role = new Model\Role();
                $role->update($this->request->getPost());
                $this->view->id = $role->id;
                $this->redirect(BASE_PATH . APP_URI . '/roles/edit/' . $role->id . '?saved=' . time());
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
            $role = new Model\Role();
            $role->getById($id);

            if ((isset($role->id)) && (null !== $role->permissions)) {
                $permissions = unserialize($role->permissions);
                if (is_array($permissions['allow']) && (count($permissions['allow']) > 0)) {
                    foreach ($permissions['allow'] as $allow) {
                        $json[] = [
                            'resource'   => $allow['resource'],
                            'action'     => $allow['permission'],
                            'permission' => 'allow'
                        ];
                    }
                }
                if (is_array($permissions['deny']) && (count($permissions['deny']) > 0)) {
                    foreach ($permissions['deny'] as $deny) {
                        $json[] = [
                            'resource'   => $deny['resource'],
                            'action'     => $deny['permission'],
                            'permission' => 'deny'
                        ];
                    }
                }
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
            $role = new Model\Role();
            $role->remove($this->request->getPost());
        }
        $this->redirect(BASE_PATH . APP_URI . '/roles?removed=' . time());
    }

}
