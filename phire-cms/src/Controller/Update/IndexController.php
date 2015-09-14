<?php

namespace Phire\Controller\Update;

use Phire\Controller\AbstractController;

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
        //if (version_compare(\Phire\Module::VERSION, $this->sess->updates->phirecms) < 0) {
        if (version_compare(\Phire\Module::VERSION, $this->sess->updates->phirecms) >= 0) {
            if ($this->request->getQuery('update') == 1) {
                echo 'Its go time';
            } else {
                $this->prepareView('phire/update.phtml');
                $this->view->title = 'Update Phire';
                $this->view->phire_update_version = $this->sess->updates->phirecms;
                $this->send();
            }
        } else {
            $this->redirect(BASE_PATH . APP_URI);
        }
    }

}