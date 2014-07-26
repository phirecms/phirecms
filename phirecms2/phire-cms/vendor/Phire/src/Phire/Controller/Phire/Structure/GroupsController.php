<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\Structure;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Project\Project;
use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class GroupsController extends AbstractController
{

    /**
     * Constructor method to instantiate the groups controller object
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
            $viewPath = __DIR__ . '/../../../../../view/phire/structure';

            if (isset($cfg['view'])) {
                $class = get_class($this);
                if (is_array($cfg['view']) && isset($cfg['view'][$class])) {
                    $viewPath = $cfg['view'][$class];
                } else if (is_array($cfg['view']) && isset($cfg['view']['*'])) {
                    $viewPath = $cfg['view']['*'];
                } else if (is_string($cfg['view'])) {
                    $viewPath = $cfg['view'];
                }
            }
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Group index method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView('groups.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $group = new Model\FieldGroup(array('acl' => $this->project->getService('acl')));
        $group->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view->set('title', $this->view->i18n->__('Structure') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Field Groups'))
                   ->set('table', $group->table);
        $this->send();
    }

    /**
     * Group add method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView('groups.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $this->view->set('title', $this->view->i18n->__('Structure') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Field Groups') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Add'));
        $form = new Form\FieldGroup($this->request->getBasePath() . $this->request->getRequestUri(), 'post');
        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('htmlentities' => array(ENT_QUOTES, 'UTF-8'))
            );

            if ($form->isValid()) {
                $group = new Model\FieldGroup();
                $group->save($form);
                $this->view->set('id', $group->id);

                if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                    Response::redirect($this->request->getBasePath() . '/edit/' . $group->id . '?saved=' . time());
                } else if (null !== $this->request->getQuery('update')) {
                    $this->sendJson(array(
                        'redirect' => $this->request->getBasePath() . '/edit/' . $group->id . '?saved=' . time(),
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
     * Group edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $this->prepareView('groups.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));

            $group = new Model\FieldGroup();
            $group->getById($this->request->getPath(1));

            // If group is found and valid
            if (isset($group->id)) {
                $this->view->set('title', $this->view->i18n->__('Structure') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Field Groups') . ' ' . $this->view->separator . ' ' . $group->name)
                           ->set('data_title', $this->view->i18n->__('Structure') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Field Groups') . ' ' . $this->view->separator . ' ');
                $form = new Form\FieldGroup($this->request->getBasePath() . $this->request->getRequestUri());

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('htmlentities' => array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save group
                    if ($form->isValid()) {
                        $group->update($form);
                        $this->view->set('id', $group->id);

                        if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                            Response::redirect($this->request->getBasePath() . '/edit/' . $group->id . '?saved=' . time());
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
                        $group->getData(null, false)
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
     * Group remove method
     *
     * @return void
     */
    public function remove()
    {
        // Loop through and delete the groups
        if ($this->request->isPost()) {
            $group = new Model\FieldGroup();
            $group->remove($this->request->getPost());
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

