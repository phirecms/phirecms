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

class SitesController extends AbstractController
{

    /**
     * Constructor method to instantiate the sites controller object
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
     * Site index method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView('sites.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $site = new Model\Site(array('acl' => $this->project->getService('acl')));
        $site->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view->set('title', 'Configuration ' . $this->view->separator . ' ' . $this->view->i18n->__('Sites'))
                   ->set('table', $site->table);
        $this->send();
    }

    /**
     * Site add method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView('sites.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
        ));

        $this->view->set('title', $this->view->i18n->__('Configuration') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Sites') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Add'));

        $form = new Form\Site(
            $this->request->getBasePath() . $this->request->getRequestUri(), 'post'
        );

        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('htmlentities' => array(ENT_QUOTES, 'UTF-8'))
            );

            if ($form->isValid()) {
                $site = new Model\Site();
                $site->save($form);
                if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                    Response::redirect($this->request->getBasePath() . '/edit/' . $site->id . '?saved=' . time());
                } else if (null !== $this->request->getQuery('update')) {
                    $this->sendJson(array(
                        'redirect' => $this->request->getBasePath() . '/edit/' . $site->id . '?saved=' . time(),
                        'updated'  => ''
                    ));
                } else {
                    Response::redirect($this->request->getBasePath() . '?saved=' . time());
                }
            } else {
                if (null !== $this->request->getQuery('update')) {
                    $this->sendJson($form->getErrors());
                } else {
                    $this->view->set('form', $form);
                    $this->send();
                }
            }
        } else {
            $this->view->set('form', $form);
            $this->send();
        }
    }

    /**
     * Site edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $this->prepareView('sites.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav'),
            ));

            $site = new Model\Site();
            $site->getById($this->request->getPath(1));

            // If field is found and valid
            if (isset($site->id)) {
                $this->view->set('title', $this->view->i18n->__('Configuration') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Sites') . ' ' . $this->view->separator . ' ' . $site->domain)
                           ->set('data_title', $this->view->i18n->__('Configuration') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Sites') . ' ' . $this->view->separator . ' ');
                $form = new Form\Site(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post', $site->id
                );

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('htmlentities' => array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save field
                    if ($form->isValid()) {
                        $site->update($form);
                        if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                            Response::redirect($this->request->getBasePath() . '/edit/' . $site->id . '?saved=' . time());
                        } else if (null !== $this->request->getQuery('update')) {
                            $this->sendJson(array(
                                'updated' => ''
                            ));
                        } else {
                            Response::redirect($this->request->getBasePath() . '?saved=' . time());
                        }
                    // Else, re-render the form with errors
                    } else {
                        if (null !== $this->request->getQuery('update')) {
                            $this->sendJson($form->getErrors());
                        } else {
                            $this->view->set('form', $form);
                            $this->send();
                        }
                    }
                // Else, render form
                } else {
                    $form->setFieldValues(
                        $site->getData(null, false)
                    );
                    $this->view->set('form', $form);
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect($this->request->getBasePath());
            }
        }
    }

    /**
     * Site remove method
     *
     * @return void
     */
    public function remove()
    {
        // Loop through and delete the fields
        if ($this->request->isPost()) {
            $site = new Model\Site();
            $site->remove($this->request->getPost());
        }

        Response::redirect($this->request->getBasePath() . '?removed=' . time());
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

