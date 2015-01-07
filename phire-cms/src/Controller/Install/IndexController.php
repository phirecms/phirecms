<?php

namespace Phire\Controller\Install;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;

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
            $this->redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
        }

        $this->prepareView('install.phtml');
        $this->view->title = 'Installation';

        $this->view->form = new Form\Install($this->application->config()['forms']['Phire\Form\Install']);

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $install = new Model\Install();
                $install->installDb($this->view->form->getFields());
                $config  = $install->createConfig($this->view->form->getFields());

                if (is_writable(__DIR__ . '/../../../../config.php')) {
                    file_put_contents(__DIR__ . '/../../../../config.php', $config);
                    $this->sess->app_uri = (!empty($this->view->form->app_uri) && ($this->view->form->app_uri != '/')) ?
                        $this->view->form->app_uri : '';
                    $this->redirect(BASE_PATH . $this->sess->app_uri . '/install/user');
                } else {
                    $this->sess->config  = htmlentities($config, ENT_QUOTES, 'UTF-8');
                    $this->sess->app_uri = (!empty($this->view->form->app_uri) && ($this->view->form->app_uri != '/')) ?
                        $this->view->form->app_uri : '';
                    $this->redirect(BASE_PATH . APP_URI . '/install/config');
                }
            }
        }

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
            $this->redirect(BASE_PATH . APP_URI . '/install');
        }

        $this->prepareView('install.phtml');
        $this->view->title = 'Install Configuration File';

        $this->view->form = new Form\InstallConfig(
            $this->sess->config, $this->application->config()['forms']['Phire\Form\InstallConfig']
        );

        if ($this->request->isPost()) {
            if ($this->view->form->isValid()) {
                unset($this->sess->config);
                $this->redirect(BASE_PATH . $this->sess->app_uri . '/install/user');
            }
        }

        $this->send();
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

        $this->view->form = new Form\Register(
            2001, false, false, $this->application->config()['forms']['Phire\Form\Register']
        );

        if ($this->request->isPost()) {
            $this->view->form->addFilter('strip_tags')
                 ->addFilter('htmlentities', [ENT_QUOTES, 'UTF-8'])
                 ->setFieldValues($this->request->getPost());

            if ($this->view->form->isValid()) {
                $this->view->form->clearFilters()
                     ->addFilter('html_entity_decode', [ENT_QUOTES, 'UTF-8'])
                     ->filter();

                $fields = $this->view->form->getFields();
                $fields['verified'] = 1;

                $user = new Model\User();
                $user->save($fields);

                $install = new Model\Install();
                $install->sendConfirmation($user);

                $this->sess->kill();
                $this->redirect(BASE_PATH . APP_URI . '/login?installed=' . time());
            }
        }

        $this->send();
    }

}