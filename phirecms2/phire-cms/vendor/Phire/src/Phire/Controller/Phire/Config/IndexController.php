<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\Config;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Project\Project;
use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends AbstractController
{

    /**
     * Constructor method to instantiate the config controller object
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Project  $project
     * @param  string   $viewPath
     * @return self
     */
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        if (null === $viewPath) {
            $cfg = $project->module('Phire')->asArray();
            $viewPath = __DIR__ . '/../../../../../view/phire/config';

            if (isset($cfg['view'])) {
                $class = get_class($this);
                if (is_array($cfg['view']) && isset($cfg['view'][$class])) {
                    $viewPath = $cfg['view'][$class];
                } else if (is_array($cfg['view']) && isset($cfg['view']['*'])) {
                    $viewPath = $cfg['view']['*'] . '/config';
                } else if (is_string($cfg['view'])) {
                    $viewPath = $cfg['view'] . '/config';
                }
            }
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Config index method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView('index.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $this->view->set('title', $this->view->i18n->__('Configuration'));

        $config = new Model\Config(array('acl' => $this->project->getService('acl')));

        if ($this->request->isPost()) {
            $config->update($this->request->getPost());
            Response::redirect($this->request->getBasePath() . '?saved=' . time());
        } else {
            $config->getAll();
            $this->view->merge($config->getData());
            $this->send();
        }
    }

    /**
     * Config update method
     *
     * @return void
     */
    public function update()
    {
        $this->prepareView('update.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        if (null !== $this->request->getQuery('module')) {
            $type      = 'module';
            $name      = $this->request->getQuery('module');
            $version   = null;
            $title     = $this->view->i18n->__('Module Update') . ' ' . $this->view->separator . ' ' . $name;
            $linkParam = '&module=' . $name;
            $ext       = Table\Extensions::findBy(array('name' => $name));
            if (isset($ext->id)) {
                $assets = unserialize($ext->assets);
                $version = $assets['info']['Version'];
            }
        } else if (null !== $this->request->getQuery('theme')) {
            $type      = 'theme';
            $name      = $this->request->getQuery('theme');
            $version   = null;
            $title     = $this->view->i18n->__('Theme Update') . ' ' . $this->view->separator . ' ' . $name;
            $linkParam = '&theme=' . $name;
            $ext       = Table\Extensions::findBy(array('name' => $name));
            if (isset($ext->id)) {
                $assets = unserialize($ext->assets);
                $version = $assets['info']['Version'];
            }
        } else {
            $type      = 'system';
            $name      = 'phire';
            $version   = \Phire\Project::VERSION;
            $title     = $this->view->i18n->__('System Update');
            $linkParam = null;
        }

        $format  = null;
        $formats = \Pop\Archive\Archive::formats();
        if (isset($formats['zip'])) {
            $format = 'zip';
        } else if (isset($formats['tar']) && isset($formats['gz'])) {
            $format = 'tar.gz';
        }

        $this->view->set('title', $this->view->i18n->__('Configuration') . ' ' . $this->view->separator . ' ' . $title);
        $config = new Model\Config();

        $writable = false;
        if (($type == 'system') && is_writable(__DIR__ . '/../../../../../../../')) {
            $writable = true;
        } else if (($type == 'module') && is_writable(__DIR__ . '/../../../../../../../../' . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules')) {
            $writable = true;
        } else if (($type == 'theme') && is_writable(__DIR__ . '/../../../../../../../../' . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes')) {
            $writable = true;
        }

        if ($writable) {
            if (null !== $this->request->getQuery('writable')) {
                $config->getUpdate(array(
                    'type'    => $type,
                    'name'    => $name,
                    'version' => $version,
                    'format'  => $format
                ));
                if (null !== $config->error) {
                    $this->view->set('msg', $config->error);
                    $this->send();
                } else {
                    $this->view->set('msg', $config->msg);
                    $this->send();
                }
            } else {
                $link = $this->request->getFullUri() . '?writable=1' . $linkParam;
                $form = '<div id="update-form">' .
                        $this->view->i18n->__('The %1 folder has been detected as writable.', $type) . ' ' .
                        $this->view->i18n->__('You can proceed with the %1 update by clicking the update button below.', $type) .
                        '<br /><br /><a href="' . $link . '" class="save-btn" style="display: block; width: 220px; height: 20px; color: #fff; text-decoration: none;">' . $this->view->i18n->__('UPDATE') . '</a></div>';
                $this->view->set('form', $form);
                $this->send();
            }
        } else {
            $form = new Form\Update(
                $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                $type, $name, $version, $format
            );
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array('htmlentities' => array(ENT_QUOTES, 'UTF-8'))
                );

                if ($form->isValid()) {
                    $config->getUpdate($this->request->getPost());
                    if (null !== $config->error) {
                        $this->view->set('msg', $config->error);
                        $this->send();
                    } else {
                        $this->view->set('msg', $config->msg);
                        $this->send();
                    }
                } else {
                    $this->view->set('form', $form);
                    $this->send();
                }
            } else {
                $this->view->set('form', $form);
                $this->send();
            }
        }
    }

    /**
     * Method to get date format
     *
     * @return void
     */
    public function json()
    {
        if (null !== $this->request->getPath(1)) {
            $format = str_replace('_', '/', urldecode($this->request->getPath(1)));

            // Build the response and send it
            $response = new Response();
            $response->setHeader('Content-Type', 'application/json')
                     ->setBody(json_encode(array('format' => date($format))));
            $response->send();
        }
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $this->prepareView('error.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $this->view->set('title', $this->view->i18n->__('404 Error') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Page Not Found'));
        $this->send(404);
    }

}

