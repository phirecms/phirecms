<?php

namespace Phire\Controller\Install;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;
use Pop\Http\Response;

class IndexController extends AbstractController
{

    public function index()
    {
        if (($this->services->isAvailable('database')) && count($this->services['database']->getTables()) > 0) {
            Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
            exit();
        }

        $this->prepareView('install.phtml');
        $this->view->title = 'Installation';

        $form = new Form\Install();

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $install = new Model\Install();
                $install->installDb($form->getFields());
                $config  = $install->createConfig($form->getFields());

                if (is_writable(__DIR__ . '/../../../../config.php')) {
                    file_put_contents(__DIR__ . '/../../../../config.php', $config);
                    Response::redirect(BASE_PATH . APP_URI . '/install/user');
                    exit();
                } else {
                    $this->sess->config = htmlentities($config, ENT_QUOTES, 'UTF-8');
                    Response::redirect(BASE_PATH . APP_URI . '/install/config');
                    exit();
                }
            }
        }

        $this->view->form = $form;
        $this->response->setBody($this->view->render());
        $this->send();
    }

    public function config()
    {
        if (!isset($this->sess->config)) {
            $this->sess->kill();
            Response::redirect(BASE_PATH . APP_URI . '/install');
            exit();
        }

        $this->prepareView('install.phtml');
        $this->view->title = 'Install Configuration File';

        $form = new Form\InstallConfig($this->sess->config);

        if ($this->request->isPost()) {
            if ($form->isValid()) {
                unset($this->sess->config);
                Response::redirect(BASE_PATH . APP_URI . '/install/user');
                exit();
            } else {
                $this->view->form = $form;
                $this->response->setBody($this->view->render());
                $this->send();
            }
        } else {
            $this->view->form = $form;
            $this->response->setBody($this->view->render());
            $this->send();
        }
    }

    public function user()
    {
        $this->prepareView('install.phtml');
        $this->view->title = 'Install User';

        $form = new Form\Register(2001);

        if ($this->request->isPost()) {
            $form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($form->isValid()) {
                $form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $fields = $form->getFields();
                $fields['verified'] = 1;

                $user = new Model\User();
                $user->save($fields);

                $install = new Model\Install();
                $install->sendConfirmation($user);

                Response::redirect(BASE_PATH . APP_URI . '/login');
            } else {
                $this->view->form = $form;
                $this->response->setBody($this->view->render());
                $this->send();
            }
        } else {
            $this->view->form = $form;
            $this->response->setBody($this->view->render());
            $this->send();
        }
    }

}