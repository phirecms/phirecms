<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\User;

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
     * Constructor method to instantiate the categories controller object
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
            $viewPath = __DIR__ . '/../../../../../view/phire/user';

            if (isset($cfg['view'])) {
                $class = get_class($this);
                if (is_array($cfg['view']) && isset($cfg['view'][$class])) {
                    $viewPath = $cfg['view'][$class];
                } else if (is_array($cfg['view']) && isset($cfg['view']['*'])) {
                    $viewPath = $cfg['view']['*'] . '/user';
                } else if (is_string($cfg['view'])) {
                    $viewPath = $cfg['view'] . '/user';
                }
            }
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Users index method
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

        $this->view->set('title', $this->view->i18n->__('Users'));

        $user = new Model\User(array('acl' => $this->project->getService('acl')));

        // If type id is set, get users by type
        if ((null !== $this->request->getPath(1)) && is_numeric($this->request->getPath(1))) {
            $user->getAll($this->request->getPath(1), $this->project->module('Phire'), $this->request->getQuery('sort'), $this->request->getQuery('page'));
            $this->view->set('typeId', $this->request->getPath(1))
                       ->set('table', $user->table)
                       ->set('searchBy', $user->searchBy)
                       ->set('searchFor', $user->searchFor)
                       ->set('title', $this->view->title . ' '. $this->view->separator . ' '. $user->title);
        // Else, list user types to choose from
        } else {
            $this->view->set('typeId', null)
                       ->set('types', $user->getUserTypes());
        }

        $this->send();
    }

    /**
     * User add method
     *
     * @return void
     */
    public function add()
    {
        $this->prepareView('add.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        // Select user type
        if (null === $this->request->getPath(1)) {
            $this->view->set('title', $this->view->i18n->__('Users') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Select Type'));
            $form = new Form\User(
                $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                '0', false, 0, $this->project->getService('acl')
            );

            // If form is submitted
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array(
                        'strip_tags'   => null,
                        'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                    )
                );

                // If form is valid, redirect to the second part of the form
                if ($form->isValid()) {
                    Response::redirect($this->request->getBasePath() . $this->request->getRequestUri() . '/' . $form->type_id);
                // Else, re-render the form with errors
                } else {
                    $this->view->set('form', $form);
                    $this->send();
                }
            // Else, render the form
            } else {
                $this->view->set('form', $form);
                $this->send();
            }
        // Else, add user
        } else {
            $type = Table\UserTypes::findById($this->request->getPath(1));

            // If user type is valid
            if (isset($type->id)) {
                $this->view->set('title', $this->view->i18n->__('Users') . ' ' . $this->view->separator . ' ' . ucwords(str_replace('-', ' ', $type->type)) . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Add'))
                           ->set('typeId', $type->id);
                $form = new Form\User(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $type->id, false, 0
                );

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array(
                            'strip_tags'   => null,
                            'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                        )
                    );

                    // If form is valid, save new user
                    if ($form->isValid()) {
                        $user = new Model\User();
                        $user->save($form, $this->project->module('Phire'));
                        $this->view->set('id', $user->id);

                        if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                            Response::redirect($this->request->getBasePath() . '/edit/' . $user->id . '?saved=' . time());
                        } else if (null !== $this->request->getQuery('update')) {
                            $this->sendJson(array(
                                'redirect' => $this->request->getBasePath() . '/edit/' . $user->id . '?saved=' . time(),
                                'updated'  => '',
                                'form'     => 'user-form'
                            ));
                        } else {
                            Response::redirect($this->request->getBasePath() . '/index/' . $this->request->getPath(1) . '?saved=' . time());
                        }
                    // Else, re-render form with errors
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
                    $this->view->set('form', $form);
                    $this->send();
                }
            // Else, redirect
            } else {
                Response::redirect($this->request->getBasePath() . '/add');
            }
        }
    }

    /**
     * User edit method
     *
     * @return void
     */
    public function edit()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $this->prepareView('edit.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));

            $user = new Model\User();
            $user->getById($this->request->getPath(1));

            // If user is found and valid
            if ((null !== $user->id) && ($this->view->acl->isAuth('Phire\Controller\Phire\Users\IndexController', 'edit')) && ($this->view->acl->isAuth('Phire\Controller\Phire\Users\IndexController', 'edit_' . $user->type_id))) {
                $this->view->set('title', $this->view->i18n->__('Users') . ' ' . $this->view->separator . ' ' . $user->type_name . ' ' . $this->view->separator . ' ' . $user->username)
                           ->set('data_title', $this->view->i18n->__('Users') . ' ' . $this->view->separator . ' ' . $user->type_name . ' ' . $this->view->separator . ' ')
                           ->set('typeId', $user->type_id);
                $form = new Form\User(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    $user->type_id, false, $user->id
                );

                // If form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(
                        $this->request->getPost(),
                        array(
                            'strip_tags'   => null,
                            'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                        )
                    );

                    // If form is valid, save the user
                    if ($form->isValid()) {
                        $user->update($form, $this->project->module('Phire'));
                        $this->view->set('id', $user->id);

                        if (null !== $this->request->getPost('update_value') && ($this->request->getPost('update_value') == '1')) {
                            Response::redirect($this->request->getBasePath() . '/edit/' . $user->id . '?saved=' . time());
                        } else if (null !== $this->request->getQuery('update')) {
                            $this->sendJson(array(
                                'updated' => '',
                                'form'    => 'user-form'
                            ));
                        } else {
                            Response::redirect($this->request->getBasePath() . '/index/' . $form->type_id . '?saved=' . time());
                        }
                    // Else, re-render form with errors
                    } else {
                        if (null !== $this->request->getQuery('update')) {
                            $this->sendJson($form->getErrors());
                        } else {
                            $this->view->set('form', $form);
                            $this->send();
                        }
                    }
                // Else, render the form
                } else {
                    $userData = $user->getData(null, false);
                    $userData['site_ids'] = (null !== $userData['site_ids']) ? unserialize($userData['site_ids']) : array();
                    $form->setFieldValues($userData);
                    $this->view->set('form', $form);
                    $this->send();
                }
            // Else redirect
            } else {
                Response::redirect($this->request->getBasePath());
            }
        }
    }

    /**
     * User edit type method
     *
     * @return void
     */
    public function type()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            $this->prepareView('type.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav')
            ));

            $user = new Model\User();
            $user->getById($this->request->getPath(1));

            // If user is found and valid
            if (null !== $user->id) {
                $this->view->set('title', $this->view->i18n->__('Users') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Type') .' ' . $this->view->separator . ' ' . $user->username)
                           ->set('typeId', $user->type_id);
                $form = new Form\User(
                    $this->request->getBasePath() . $this->request->getRequestUri(), 'post',
                    0, false, 0, $this->project->getService('acl')
                );

                // If the form is submitted
                if ($this->request->isPost()) {
                    $form->setFieldValues(array('type_id' => $this->request->getPost('type_id')));

                    // If the form is valid, save user type
                    if ($form->isValid()) {
                        $user->updateType($form, $this->project->module('Phire'));
                        Response::redirect($this->request->getBasePath() . '?saved=' . time());
                    // Else, re-render the form with errors
                    } else {
                        $this->view->set('form', $form);
                        $this->send();
                    }
                // Else, render the form
                } else {
                    $form->setFieldValues(array('type_id' => $user->type_id));
                    $this->view->set('form', $form);
                    $this->send();
                }
            // Else redirect
            } else {
                Response::redirect($this->request->getBasePath());
            }
        }
    }

    /**
     * User logins method
     *
     * @return void
     */
    public function logins()
    {
        if (null === $this->request->getPath(1)) {
            Response::redirect($this->request->getBasePath());
        } else {
            if ($this->request->isPost()) {
                $user = Table\Users::findById($this->request->getPath(1));
                if (isset($user->id)) {
                    $user->logins = null;
                    $user->update();
                }
                $typeId = (null !== $this->request->getQuery('type_id')) ? '/index/' . $this->request->getQuery('type_id') : null;
                Response::redirect($this->request->getBasePath() . $typeId);
            } else {
                $this->prepareView('logins.phtml', array(
                    'assets'   => $this->project->getAssets(),
                    'acl'      => $this->project->getService('acl'),
                    'phireNav' => $this->project->getService('phireNav')
                ));

                $user = new Model\User();
                $user->getLoginsById($this->request->getPath(1));
                $this->view->set('title', $this->view->i18n->__('Users') . ' ' . $this->view->separator . ' ' . $user->type_name . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Logins') . ' ' . $this->view->separator . ' ' . $user->username)
                           ->set('typeId', $user->type_id)
                           ->set('table', $user->table);
                $this->send();
            }
        }
    }

    /**
     * User remove method
     *
     * @return void
     */
    public function remove()
    {
        $typeId = (null !== $this->request->getPath(1)) ? '/index/' . $this->request->getPath(1) : null;

        // Loop through the users to delete
        if ($this->request->isPost()) {
            $user = new Model\User();
            $user->remove($this->request->getPost());
        }

        Response::redirect($this->request->getBasePath() . $typeId . '?removed=' . time());
    }

    /**
     * Export method
     *
     * @return void
     */
    public function export()
    {
        $user = new Model\User();
        $user->getExport(
            $this->request->getPath(1),
            $this->request->getQuery('sort'),
            $this->request->getQuery('page')
        );

        if (isset($user->userRows[0])) {
            $userRows = $user->userRows;
            foreach ($userRows as $key => $value) {
                foreach ($value as $k => $v) {
                    if (is_array($v)) {
                        $userRows[$key]->{$k} = implode('|', $v);
                    }
                }
            }
            \Pop\Data\Data::factory($userRows)->writeData($_SERVER['HTTP_HOST'] . '_' . $user->userType . '_' . date('Y-m-d') . '.csv', true, true);
        } else {
            Response::redirect($this->request->getBasePath() . '/index/' . $this->request->getPath(1));
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

