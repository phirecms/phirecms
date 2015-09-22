<?php

namespace Phire\Controller\Update;

use Pop\Archive\Archive;
use Phire\Controller\AbstractController;
use Phire\Form;

class IndexController extends AbstractController
{

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        // Switch this to < for validation when live
        if (version_compare(\Phire\Module::VERSION, $this->sess->updates->phirecms) < 0) {
            if ($this->request->getQuery('update') == 1) {
                file_put_contents(__DIR__ . '/../../../../phirecms.zip', fopen('http://updates.phirecms.org/releases/phire/phirecms.zip', 'r'));
                $basePath = realpath(__DIR__ . '/../../../../');
                $archive  = new Archive($basePath . '/phirecms.zip');
                $archive->extract($basePath);
                unlink(__DIR__ . '/../../../../phirecms.zip');
                echo 'Done!';
            } else {
                $this->prepareView('phire/update.phtml');
                $this->view->title = 'Update Phire';
                $this->view->phire_update_version = $this->sess->updates->phirecms;
                if (is_writable(__DIR__ . '/../../../../')) {
                    $this->view->form = false;
                } else {
                    $this->view->form = new Form\Update($this->application->config()['forms']['Phire\Form\Update']);
                }

                $this->send();
            }
        } else {
            $this->redirect(BASE_PATH . APP_URI);
        }
    }

}