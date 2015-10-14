<?php

namespace Phire\Controller\Update;

use Pop\Archive\Archive;
use Pop\File\Dir;
use Pop\Http\Client\Curl;
use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Updater;
use Pop\Http\Response;

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
            if (($this->request->getQuery('update') == 1) && is_writable(__DIR__ . '/../../../../') && is_writable(__DIR__ . '/../../../..' . APP_PATH)) {
                file_put_contents(__DIR__ . '/../../../../' . CONTENT_PATH . '/updates/phire-cms.zip', fopen('http://updates.phirecms.org/releases/phire/phire-cms.zip', 'r'));
                $basePath = realpath(__DIR__ . '/../../../../' . CONTENT_PATH . '/updates/');
                $archive  = new Archive($basePath . '/phire-cms.zip');
                $archive->extract($basePath);
                unlink(__DIR__ . '/../../../../' . CONTENT_PATH . '/updates/phire-cms.zip');
                $json = json_decode(stream_get_contents(fopen('http://updates.phirecms.org/releases/phire/phire.json', 'r')), true);
                foreach ($json as $file) {
                    echo 'Updating: ' . $file . '<br />' . PHP_EOL;
                    copy(__DIR__ . '/../../../../' . CONTENT_PATH . '/updates/phire-cms/' . $file, __DIR__ . '/../../../' . $file);
                }
                $dir = new Dir(__DIR__ . '/../../../../' . CONTENT_PATH . '/updates/phire-cms/');
                $dir->emptyDir(true);

                $updater = new Updater();
                $updater->run();

                echo 'Done!';
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
                            // Complete update via FTP
                            $basePath = realpath(__DIR__ . '/../../../..' . CONTENT_PATH);
                            $archive  = new Archive($basePath . '/phirecms.zip');
                            $archive->extract($basePath);
                            chmod($basePath . '/phire-cms-new', 0777);
                            unlink(__DIR__ . '/../../../..' . CONTENT_PATH . '/phirecms.zip');

                            $curl = new Curl('http://updates.phirecms.org/fetch/' . $fields['resource'] . '?move=1');
                            $curl->setFields($fields);
                            $curl->setPost(true);

                            $curl->send();
                            $json = json_decode($curl->getBody(), true);
                            if ($curl->getCode() == 401) {
                                $this->view->form = '<h4 class="error">' . $json['error'] . '</h4>';
                            } else {
                                $this->view->form = '<h4 class="required">' . $json['message'] . '</h4>';
                            }
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
     * Run system updater action method
     *
     * @return void
     */
    public function run()
    {

    }

}