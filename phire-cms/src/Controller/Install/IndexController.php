<?php

namespace Phire\Controller\Install;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;
use Pop\Http\Response;

class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        if (($this->services->isAvailable('database')) && count($this->services['database']->getTables()) > 0) {
            Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
            exit();
        }

        $this->prepareView('install.phtml');
        $this->view->title = 'Installation';

        $form = new Form\Install($this->application->config()['forms']['Phire\Form\Install']);

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
                    $this->sess->app_uri = (!empty($form->app_uri) && ($form->app_uri != '/')) ? $form->app_uri : '';
                    Response::redirect(BASE_PATH . $this->sess->app_uri . '/install/user');
                    exit();
                } else {
                    $this->sess->config  = htmlentities($config, ENT_QUOTES, 'UTF-8');
                    $this->sess->app_uri = (!empty($form->app_uri) && ($form->app_uri != '/')) ? $form->app_uri : '';
                    Response::redirect(BASE_PATH . APP_URI . '/install/config');
                    exit();
                }
            }
        }

        $this->view->form = $form;
        $this->send();
    }

    /**
     * Config action method
     *
     * @return void
     */
    public function config()
    {
        if (!isset($this->sess->config)) {
            $this->sess->kill();
            Response::redirect(BASE_PATH . APP_URI . '/install');
            exit();
        }

        $this->prepareView('install.phtml');
        $this->view->title = 'Install Configuration File';

        $form = new Form\InstallConfig($this->sess->config, $this->application->config()['forms']['Phire\Form\InstallConfig']);

        if ($this->request->isPost()) {
            if ($form->isValid()) {
                unset($this->sess->config);
                Response::redirect(BASE_PATH . $this->sess->app_uri . '/install/user');
                exit();
            } else {
                $this->view->form = $form;
                $this->send();
            }
        } else {
            $this->view->form = $form;
            $this->send();
        }
    }

    /**
     * User action method
     *
     * @return void
     */
    public function user()
    {
        $this->prepareView('install.phtml');
        $this->view->title = 'Install User';

        $form = new Form\Register(2001, false, false, $this->application->config()['forms']['Phire\Form\Register']);

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

                $this->sess->kill();
                Response::redirect(BASE_PATH . APP_URI . '/login?installed=' . time());
            } else {
                $this->view->form = $form;
                $this->send();
            }
        } else {
            $this->view->form = $form;
            $this->send();
        }
    }

}