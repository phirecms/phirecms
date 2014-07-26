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

class FieldsController extends AbstractController
{

    /**
     * Constructor method to instantiate the fields controller object
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
     * Field index method
     *
     * @return void
     */
    public function index()
    {
        $this->prepareView('fields.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $field = new Model\Field(array('acl' => $this->project->getService('acl')));
        $field->getAll($this->request->getQuery('sort'), $this->request->getQuery('page'));
        $this->view->set('title', $this->view->i18n->__('Structure') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Fields'))
                   ->set('table', $field->table);
        $this->send();
    }

    /**
     * Field add method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView('fields.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
        ));

        $this->view->set('title', $this->view->i18n->__('Structure') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Fields') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Add'));

        $form = new Form\Field(
            $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
            0, $this->project->module('Phire')
        );
        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array('htmlentities' => array(ENT_QUOTES, 'UTF-8'))
            );

            if ($form->isValid()) {
                $field = new Model\Field();
                $field->save($form);
                $this->view->set('id', $field->id);

                if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                    Response::redirect($this->request->getBasePath() . '/edit/' . $field->id . '?saved=' . time());
                } else if (null !== $this->request->getQuery('update')) {
                    $this->sendJson(array(
                        'redirect' => $this->request->getBasePath() . '/edit/' . $field->id . '?saved=' . time(),
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
     * Field edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $this->prepareView('fields.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav'),
            ));

            $field = new Model\Field();
            $field->getById($this->request->getPath(1));

            // If field is found and valid
            if (isset($field->id)) {
                $this->view->set('title', $this->view->i18n->__('Structure') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Fields') . ' ' . $this->view->separator . ' ' . $field->name)
                           ->set('data_title', $this->view->i18n->__('Structure') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Fields') . ' ' . $this->view->separator . ' ');
                $form = new Form\Field(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $field->id, $this->project->module('Phire')
                );

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array('htmlentities' => array(ENT_QUOTES, 'UTF-8'))
                    );

                    // If form is valid, save field
                    if ($form->isValid()) {
                        $field->update($form);
                        $this->view->set('id', $field->id);

                        if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                            Response::redirect($this->request->getBasePath() . '/edit/' . $field->id . '?saved=' . time());
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
                        $field->getData(null, false)
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
     * Field remove method
     *
     * @return void
     */
    public function remove()
    {
        // Loop through and delete the fields
        if ($this->request->isPost()) {
            $field = new Model\Field();
            $field->remove($this->request->getPost());
        }

        Response::redirect($this->request->getBasePath() . '?removed=' . time());
    }

    /**
     * Method to get model types
     *
     * @return void
     */
    public function json()
    {
        $body = '';

        if (null !== $this->request->getPath(1)) {
            // Get the selected field history value
            if (($this->request->getPath(1) == 'history') &&
                (null !== $this->request->getPath(2)) &&
                is_numeric($this->request->getPath(2)) &&
                (null !== $this->request->getPath(3)) &&
                is_numeric($this->request->getPath(3)) &&
                (null !== $this->request->getPath(4)) &&
                is_numeric($this->request->getPath(4))) {

                $modelId = $this->request->getPath(2);
                $fieldId = $this->request->getPath(3);
                $time = $this->request->getPath(4);
                $value = '';
                $encOptions = $this->project->module('Phire')->encryptionOptions->asArray();
                $fv = Table\FieldValues::findById(array($fieldId, $modelId));

                if (isset($fv->field_id) && (null !== $fv->history)) {
                    $history = json_decode($fv->history, true);
                    if (isset($history[$time])) {
                        $value = $history[$time];
                        $f = Table\Fields::findById($fieldId);
                        $value = Model\FieldValue::decrypt($value, $f->encryption, $encOptions);
                    }
                }
                $body = array('fieldId' => $fieldId, 'modelId' => $modelId, 'value' => html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
            // Get the field history timestamps
            } else if (($this->request->getPath(1) == 'history') &&
                (null !== $this->request->getPath(2)) &&
                is_numeric($this->request->getPath(2)) &&
                (null !== $this->request->getPath(3)) &&
                is_numeric($this->request->getPath(3))) {

                $modelId = $this->request->getPath(2);
                $fieldId = $this->request->getPath(3);

                $fv = Table\FieldValues::findById(array($fieldId, $modelId));
                if (isset($fv->field_id) && (null !== $fv->history)) {
                    $body = array_keys(json_decode($fv->history, true));
                    rsort($body);
                }
            // Get the model types
            } else {
                $clsAry = $this->request->getPath();
                unset($clsAry[0]);
                $cls = implode('_', $clsAry);
                $types = \Phire\Project::getModelTypes($cls);
                $body = array('types' => $types);
            }

            // Build the response and send it
            $response = new Response();
            $response->setHeader('Content-Type', 'application/json; charset=utf-8')
                     ->setBody(json_encode($body));
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

