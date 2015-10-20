<?php

namespace Phire\Controller\Update;

use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Updater;
use Pop\Http\Client\Curl;

class IndexController extends AbstractController
{

    /**
     * Update URL
     * @var string
     */
    protected $url = 'http://updates.phirecms.org/releases/phire/phirecms.zip';

    /**
     * Index action method
     *
     * @return void
     */
    public function index()
    {
        // Switch this to < for validation when live
        if (version_compare(\Phire\Module::VERSION, $this->sess->updates->phirecms) == 0) {
            // Complete one-click updating
            if (($this->request->getQuery('update') == 1) &&
                is_writable(__DIR__ . '/../../../../') && is_writable(__DIR__ . '/../../../..' . APP_PATH)) {
                $updater = new Updater('phire');
                $updater->runUpdate();

                clearstatcache();

                $updater = new Updater('phire');
                $updater->runPost();
                $this->redirect(BASE_PATH . APP_URI . '/update/complete');
            // Else, use FTP for updating
            } else {
                $this->prepareView('phire/update.phtml');
                $this->view->title = 'Update Phire';
                $this->view->url   = $this->url;
                $this->view->phire_update_version = $this->sess->updates->phirecms;

                // Detect one-click updating
                if (is_writable(__DIR__ . '/../../../../') && is_writable(__DIR__ . '/../../../..' . APP_PATH)) {
                    $this->view->form = false;
                } else {
                    $fields = $this->application->config()['forms']['Phire\Form\Update'];
                    $fields[1]['resource']['value'] = 'phirecms';
                    $this->view->form = new Form\Update($fields);
                }

                // Start update via FTP
                if (($this->view->form !== false) && ($this->request->isPost())) {
                    $this->view->form->addFilter('strip_tags')
                         ->setFieldValues($this->request->getPost());

                    if ($this->view->form->isValid()) {
                        $fields = $this->view->form->getFields();

                        $curl = new Curl('http://updates.phirecms.org/fetch/' . $fields['resource']);
                        $curl->setFields($fields);
                        $curl->setPost(true);

                        $curl->send();
                        $json = json_decode($curl->getBody(), true);

                        if ($curl->getCode() == 401) {
                            $this->view->form = '<h4 class="error">' . $json['error'] . '</h4>';
                        } else {
                            clearstatcache();
                            $updater = new Updater('phire');
                            $updater->runPost();
                            $this->redirect(BASE_PATH . APP_URI . '/update/complete');
                        }
                    }
                }

                $this->send();
            }
        } else {
            $this->redirect(BASE_PATH . APP_URI);
        }
    }

    /**
     * Complete action method
     *
     * @return void
     */
    public function complete()
    {
        $this->prepareView('phire/update.phtml');
        $this->view->title    = 'Update Phire : Complete!';
        $this->view->complete = true;
        $this->view->version  = \Phire\Module::VERSION;
        $this->send();
    }

}