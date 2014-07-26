<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire;

use Pop\Auth;
use Pop\Http\Response;
use Pop\Http\Request;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Controller\AbstractController;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends AbstractController
{

    /**
     * Session property
     * @var \Pop\Web\Session
     */
    protected $sess = null;

    /**
     * Types property
     * @var \Phire\Table\UserTypes
     */
    protected $type = null;

    /**
     * Constructor method to instantiate the user controller object
     *
     * @param  Request  $request
     * @param  Response $response
     * @param  Project  $project
     * @param  string   $viewPath
     * @return self
     */
    public function __construct(Request $request = null, Response $response = null, Project $project = null, $viewPath = null)
    {
        // Create the session object and get the user type
        $this->sess = Session::getInstance();
        $this->type = $project->getService('acl')->getType();

        if (null === $viewPath) {
            $cfg = $project->module('Phire')->asArray();
            $viewPath = __DIR__ . '/../../../../view/phire';

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

            // If it is not a user, or a user globally logged into another area
            if (((strtolower($this->type->type) != 'user') && (!$this->type->global_access)) ||
                (substr($_SERVER['REQUEST_URI'], 0, strlen(BASE_PATH . APP_URI)) != BASE_PATH . APP_URI)) {
                $site            = Table\Sites::getSite();
                $theme           = Table\Extensions::findBy(array('type' => 0, 'active' => 1), null, 1);
                $themePath       = $site->document_root . $site->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $this->type->type;
                $activeThemePath = null;

                if (isset($theme->rows[0])) {
                    $activeThemePath = $site->document_root . $site->base_path . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $theme->rows[0]->name . DIRECTORY_SEPARATOR . $this->type->type;
                }
                if ((null !== $activeThemePath) && file_exists($activeThemePath)) {
                    $viewPath = $activeThemePath;
                } else if (file_exists($themePath)) {
                    $viewPath = $themePath;
                }
            }
        }

        // Set the correct base path and user URI based on user type
        if (get_called_class() == 'Phire\Controller\Phire\IndexController') {
            $basePath = (strtolower($this->type->type) != 'user') ? BASE_PATH . '/' . strtolower($this->type->type) : BASE_PATH . APP_URI;
            $request = new Request(null, $basePath);
        }

        parent::__construct($request, $response, $project, $viewPath);
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        $config     = new Model\Config(array('acl' => $this->project->getService('acl')));
        $extensions = new Model\Extension();

        $this->prepareView('index.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav')
        ));

        $this->view->set('title', $this->view->i18n->__('Dashboard'));

        if (isset($this->sess->sessionError)) {
            $this->view->set('sessionError', $this->sess->sessionError);
            unset($this->sess->sessionError);
        }

        $overview = $config->getOverview();

        $this->view->set('modules', $extensions->getAllModules())
                   ->set('overview', $overview['system'])
                   ->set('sites', $overview['sites']);

        $this->send();
    }

    /**
     * Login method
     *
     * @param  string $redirect
     * @return void
     */
    public function login($redirect = null)
    {
        $site = Table\Sites::findBy(array('document_root' => $_SERVER['DOCUMENT_ROOT']));
        // Prevent attempting to log into the system from other sites
        if (isset($site->id) && (strtolower($this->type->type) == 'user')) {
            Response::redirect('http://' . $site->domain . BASE_PATH);
        // If user type is not found, 404
        } else if (!isset($this->type->id)) {
            $this->error();
        // If login is not allowed
        } else if (!$this->type->login) {
            Response::redirect(BASE_PATH . '/');
        // Else, render the form
        } else {
            $this->prepareView('login.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav'),
                'phire'    => new Model\Phire()
            ));

            $this->view->set('title', $this->view->i18n->__('Login'));

            // Set up 'forgot,' 'register' and 'unsubscribe' links
            $uri = (strtolower($this->type->type) == 'user') ? APP_URI : '/' . strtolower($this->type->type);
            $forgot = '<a href="' . BASE_PATH . $uri . '/forgot">' . $this->view->i18n->__('Forgot') . '</a>';
            $forgot .= (($this->type->registration) ? ' | <a href="' . BASE_PATH . $uri . '/register">' . $this->view->i18n->__('Register') . '</a>' : null);
            $forgot .= (!($this->type->unsubscribe_login) ? ' | <a href="' . BASE_PATH . $uri . '/unsubscribe">' . $this->view->i18n->__('Unsubscribe') . '</a>' : null);
            $this->view->set('forgot', $forgot);

            if (isset($this->sess->expired)) {
                $this->view->set('error', $this->view->i18n->__('Your session has expired.'));
            } else if (isset($this->sess->authError)) {
                $this->view->set('error', $this->view->i18n->__('The user is not allowed in this area.'));
            }

            $form = new Form\Login($this->request->getBasePath() . $this->request->getRequestUri(), 'post');

            // If form is submitted
            if ($this->request->isPost()) {
                $user = new Model\User();
                $form->setFieldValues(
                    $this->request->getPost(),
                    array(
                        'strip_tags'   => null,
                        'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                    ),
                    $this->project->getService('auth')->config($this->type, $this->request->getPost('username')),
                    $this->type, $user
                );

                $this->view->set('form', $form);

                // If form is valid, authenticate the user
                if ($form->isValid()) {
                    $user->login($form->username, $this->type);
                    if (isset($this->sess->lastUrl)) {
                        $url = $this->sess->lastUrl;
                    } else {
                        $url = (null !== $redirect) ? $redirect : $this->request->getBasePath();
                    }
                    unset($this->sess->expired);
                    unset($this->sess->authError);
                    unset($this->sess->lastUrl);

                    if ($url == '') {
                        $url = '/';
                    }
                    Response::redirect($url);
                // Else, re-render the form
                } else {
                    $this->send();
                }
            // Else, render the form
            } else {
                $this->view->set('form', $form);
                $this->send();
            }
        }
    }

    /**
     * Register method
     *
     * @param  string $redirect
     * @return void
     */
    public function register($redirect = null)
    {
        // If registration is not allowed
        if (!$this->type->registration) {
            Response::redirect($this->request->getBasePath());
        // Else render the registration form
        } else {
            $this->prepareView('register.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav'),
                'phire'    => new Model\Phire()
            ));

            $this->view->set('title', $this->view->i18n->__('Register'));

            $form = new Form\User(
                $this->request->getBasePath() . $this->request->getRequestUri(),
                'post', $this->type->id, true, 0, null, true
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
                    $user = new Model\User();
                    $user->save($form, $this->project->module('Phire'));
                    if (null !== $redirect) {
                        Response::redirect($redirect);
                    } else {
                        $this->view->set('form', '        <h4>Thank you for registering.</h4>')
                                   ->set('typeUri', ((strtolower($this->type->type) != 'user') ? '/' . strtolower($this->type->type) : APP_URI));
                        if ($this->type->verification) {
                            $this->view->set('verify', true);
                        }
                        if ($this->type->approval) {
                            $this->view->set('approval', true);
                        }
                        $this->send();
                    }
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
        }
    }

    /**
     * Profile method
     *
     * @param  string $redirect
     * @return void
     */
    public function profile($redirect = null)
    {
        $this->prepareView('profile.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'phire'    => new Model\Phire()
        ));

        $this->view->set('title', $this->view->i18n->__('Profile'));

        if (isset($this->sess->reset_pwd)) {
            $this->view->set('reset', $this->view->i18n->__('You must reset your password before continuing.'));
        }

        $user = new Model\User();
        $user->getById($this->sess->user->id);

        // If user is found and valid
        if (null !== $user->id) {
            $form = new Form\User(
                $this->request->getBasePath() . $this->request->getRequestUri(),
                'post', $this->type->id, true, $user->id
            );

            // If the form is submitted
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array(
                        'strip_tags'   => null,
                        'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                    ),
                    $this->project->module('Phire')
                );

                // If the form is valid
                if ($form->isValid()) {
                    $user->update($form, $this->project->module('Phire'));
                    $url = (null !== $redirect) ? $redirect : $this->request->getBasePath();
                    if ($url == '') {
                        $url = '/';
                    }
                    Response::redirect($url);
                // Else, re-render the form with errors
                } else {
                    $this->view->set('form', $form);
                    $this->send();
                }
            // Else, render the form
            } else {
                $form->setFieldValues(
                    $user->getData(null, false)
                );
                $this->view->set('form', $form);
                $this->send();
            }
        }
    }

    /**
     * Unsubscribe method
     *
     * @param  string $redirect
     * @return void
     */
    public function unsubscribe($redirect = null)
    {
        $this->prepareView('unsubscribe.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'phire'    => new Model\Phire()
        ));

        $this->view->set('title', $this->view->i18n->__('Unsubscribe'));

        $form = new Form\Unsubscribe($this->request->getBasePath() . $this->request->getRequestUri(), 'post');

        // If form is submitted
        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array(
                    'strip_tags'   => null,
                    'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                )
            );

            // If form is valid, unsubscribe the user
            if ($form->isValid()) {
                $user = new Model\User();
                $user->unsubscribe($form);
                if ($this->project->getService('acl')->isAuth()) {
                    $this->logout(false);
                }
                if (null !== $redirect) {
                    Response::redirect($redirect);
                } else {
                    $this->prepareView('unsubscribe.phtml', array(
                        'assets'   => $this->project->getAssets()
                    ));
                    $this->view->set('title', $this->view->i18n->__('Unsubscribe'));
                    $this->view->set('form', '    <p>' . $this->view->i18n->__('Thank you. You have been unsubscribed from this website.') . '</p>');
                    $this->send();
                }
            // Else, re-render the form with errors
            } else {
                $this->view->set('form', $form);
                $this->send();
            }
        // Else, render the form
        } else {
            if ($this->project->getService('acl')->isAuth()) {
                $form->setFieldValues(array('email' => $this->sess->user->email));
            }
            $this->view->set('form', $form);
            $this->send();
        }
    }

    /**
     * Forgot method
     *
     * @param  string $redirect
     * @return void
     */
    public function forgot($redirect = null)
    {
        $this->prepareView('forgot.phtml', array(
            'assets'   => $this->project->getAssets(),
            'acl'      => $this->project->getService('acl'),
            'phireNav' => $this->project->getService('phireNav'),
            'phire'    => new Model\Phire()
        ));

        $this->view->set('title', $this->view->i18n->__('Forgot'));

        $form = new Form\Forgot($this->request->getBasePath() . $this->request->getRequestUri(), 'post');

        // If form is submitted
        if ($this->request->isPost()) {
            $form->setFieldValues(
                $this->request->getPost(),
                array(
                    'strip_tags'   => null,
                    'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                )
            );

            // If form is valid, send reminder
            if ($form->isValid()) {
                $form->filter(array(
                    'strip_tags'   => null,
                    'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                ));
                $user = new Model\User();
                $user->sendReminder($form->email, $this->project->module('Phire'));
                if (null !== $redirect) {
                    Response::redirect($redirect);
                } else {
                    $this->view->set('form', '    <p>' . $this->view->i18n->__('Thank you. A password reminder has been sent.') . '</p>');
                    $this->send();
                }
            // Else, re-render the form with errors
            } else {
                $this->view->set('form', $form);
                $this->send();
            }
        // Else, render the form
        } else {
            if ($this->project->getService('acl')->isAuth()) {
                $form->setFieldValues(array('email' => $this->sess->user->email));
            }
            $this->view->set('form', $form);
            $this->send();
        }
    }

    /**
     * Verify method
     *
     * @param  string $redirect
     * @return void
     */
    public function verify($redirect = null)
    {
        // If the required user ID and hash is submitted
        if ((null !== $this->request->getPath(1)) && (null !== $this->request->getPath(2))) {
            $this->prepareView('verify.phtml', array(
                'assets'   => $this->project->getAssets(),
                'acl'      => $this->project->getService('acl'),
                'phireNav' => $this->project->getService('phireNav'),
                'phire'    => new Model\Phire(),
                'title'    => 'Verify'
            ));

            $this->view->set('title', $this->view->i18n->__('Verify'));

            $user = new Model\User();
            $user->getById($this->request->getPath(1));

            // If the user was found, verify and save
            if (isset($user->id) && (sha1($user->email) == $this->request->getPath(2))) {
                $user->verify();
                $message = 'Thank you. Your email has been verified.';
            // Else, render failure message
            } else {
                $message = 'Sorry. That email could not be verified.';
            }
            if (null !== $redirect) {
                Response::redirect($redirect);
            } else {
                $this->view->set('message', $this->view->i18n->__($message));
                $this->send();
            }
        // Else, redirect
        } else {
            Response::redirect($this->request->getBasePath());
        }
    }

    /**
     * Method to use a JSON request to reset a user's last session action
     *
     * @return void
     */
    public function session()
    {
        $session = new Model\UserSession();

        // Update user session last action
        if (isset($session->user->sess_id)) {
            $userSession = Table\UserSessions::findById($session->user->sess_id);
            if (isset($userSession->id) && ($userSession->user_id == $session->user->id)) {
                $userSession->last = date('Y-m-d H:i:s');
                $userSession->save();
            }
        }
    }

    /**
     * Logout method
     *
     * @param  boolean $redirect
     * @return void
     */
    public function logout($redirect = true)
    {
        $this->project->getService('acl')->logout($redirect);
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
            'phireNav' => $this->project->getService('phireNav'),
            'phire'    => new Model\Phire(),
        ));

        $this->view->set('title', $this->view->i18n->__('404 Error') . ' ' . $this->view->separator . ' ' . $this->view->i18n->__('Page Not Found'));
        $this->send(404);
    }

}
