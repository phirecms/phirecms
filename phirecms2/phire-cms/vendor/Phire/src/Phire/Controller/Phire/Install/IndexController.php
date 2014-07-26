<?php
/**
 * @namespace
 */
namespace Phire\Controller\Phire\Install;

use Pop\Http\Response;
use Pop\Http\Request;
use Pop\I18n\I18n;
use Pop\Mvc\Controller as C;
use Pop\Mvc\View;
use Pop\Project\Project;
use Pop\Web\Session;
use Phire\Form;
use Phire\Model;
use Phire\Table;

class IndexController extends C
{

    /**
     * Session object
     * @var \Pop\Web\Session
     */
    protected $sess = null;

    /**
     * Language object
     * @var \Pop\I18n\I18n
     */
    protected $i18n = null;

    /**
     * Constructor method to instantiate the default controller object
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
            $viewPath = __DIR__ . '/../../../../../view/phire/install';

            if (isset($cfg['view'])) {
                $class = get_class($this);
                if (is_array($cfg['view']) && isset($cfg['view'][$class])) {
                    $viewPath = $cfg['view'][$class];
                } else if (is_array($cfg['view']) && isset($cfg['view']['*'])) {
                    $viewPath = $cfg['view']['*'] . '/install';
                } else if (is_string($cfg['view'])) {
                    $viewPath = $cfg['view'] . '/install';
                }
            }
        }

        $lang = (isset($_GET['lang'])) ? $_GET['lang'] : 'en_US';
        if (!defined('POP_LANG')) {
            define('POP_LANG', $lang);
        }

        $this->i18n = I18n::factory();
        $this->i18n->loadFile($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . APP_PATH . '/vendor/Phire/data/assets/i18n/' . $this->i18n->getLanguage() . '.xml');

        parent::__construct($request, $response, $project, $viewPath);
        $this->sess = Session::getInstance();
    }

    /**
     * Index method
     *
     * @return void
     */
    public function index()
    {
        if ((DB_INTERFACE != '') && (DB_NAME != '')) {
            Response::redirect(BASE_PATH . APP_URI);
        } else {
            $install = new Model\Install(array(
                'title' => $this->i18n->__('Installation')
            ));

            $form = new Form\Install($this->request->getBasePath() . $this->request->getRequestUri() . '?lang=' . $this->i18n->getLanguage() . '_' . $this->i18n->getLocale(), 'post');

            if (!\Pop\Image\Gd::isInstalled() && !\Pop\Image\Imagick::isInstalled()) {
                $install->set('error', 'Neither the GD or Imagick extensions are installed. Phire CMS 2.0 requires one of them to be installed for graphic manipulation to fully function properly. You can continue with the install, but you will not be able to upload or manipulate image graphics until one of the image extensions is installed.');
            }

            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array(
                        'strip_tags'   => null,
                        'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                    )
                );

                if ($form->isValid()) {
                    $install->config($form);
                    $url = ($install->configWritable) ?
                        BASE_PATH . $form->app_uri . '/install/user' :
                        BASE_PATH . APP_URI . '/install/config';
                    Response::redirect($url . '?lang=' . POP_LANG);
                } else {
                    $install->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/index.phtml', $install->getData());
                    $this->view->set('i18n', $this->i18n);
                    $this->send();
                }
            } else {
                $install->set('form', $form);
                $this->view = View::factory($this->viewPath . '/index.phtml', $install->getData());
                $this->view->set('i18n', $this->i18n);
                $this->send();
            }
        }
    }

    /**
     * Config method
     *
     * @return void
     */
    public function config()
    {
        // If the config was already written, redirect to the initial user screen
        if ((DB_INTERFACE != '') && (DB_NAME != '')) {
            Response::redirect(BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install/user');
        // Else, if the initial install screen isn't complete
        } else if (!isset($this->sess->config)) {
            Response::redirect(BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install');
        // Else, display config to be copied and pasted
        } else {
            $install = new Model\Install(array(
                'title'  => $this->i18n->__('Configuration'),
                'config' => unserialize($this->sess->config),
                'uri'    => BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install/user'
            ));
            $this->view = View::factory($this->viewPath . '/config.phtml', $install->getData());
            $this->view->set('i18n', $this->i18n);
            $this->send();
        }
    }

    /**
     * Install initial user method
     *
     * @return void
     */
    public function user()
    {
        // If the system is installed
        if ((DB_INTERFACE != '') && (DB_NAME != '') && !isset($this->sess->config)) {
            Response::redirect(BASE_PATH . APP_URI);
        // Else, if the initial install screen or config isn't complete
        } else if ((DB_INTERFACE == '') && (DB_NAME == '')) {
            if (isset($this->sess->config)) {
                Response::redirect(BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install/config?lang=' . $_GET['lang']);
            } else {
                Response::redirect(BASE_PATH . (isset($this->sess->app_uri) ? $this->sess->app_uri : APP_URI) . '/install?lang=' . $_GET['lang']);
            }
        // Else, install the first system user
        } else {
            $user = new Model\User(array(
                'title' => $this->i18n->__('User Setup')
            ));
            $form = new Form\User($this->request->getBasePath() . $this->request->getRequestUri() . '?lang=' . $this->i18n->getLanguage() . '_' . $this->i18n->getLocale(), 'post', 2001, true);
            if ($this->request->isPost()) {
                $form->setFieldValues(
                    $this->request->getPost(),
                    array(
                        'strip_tags'   => null,
                        'htmlentities' => array(ENT_QUOTES, 'UTF-8')
                    )
                );

                if ($form->isValid()) {
                    $user->save($form, $this->project->module('Phire'));

                    $newUser = Table\Users::findById($user->id);
                    if (isset($newUser->id)) {
                        $newUser->site_ids = serialize(array(0));
                        $newUser->created = date('Y-m-d H:i:s');
                        $newUser->update();
                    }

                    $ext = new Model\Extension(array('acl' => $this->project->getService('acl')));
                    $ext->getModules($this->project);

                    if (count($ext->new) > 0) {
                        $ext->installModules();
                    }

                    $user->set('form', '        <p style="text-align: center; margin: 50px 0 0 0; line-height: 1.8em; font-size: 1.2em;">' . $this->i18n->__('Thank you. The system has been successfully installed.') . '<br />' . $this->i18n->__('You can now log in %1here%2 or view the home page %3here%4.', array('<a href="' . BASE_PATH . APP_URI . '/login">', '</a>', '<a href="' . BASE_PATH . '/" target="_blank">', '</a>')) . '</p>' . PHP_EOL);
                    Model\Install::send($form);
                    unset($this->sess->config);
                    unset($this->sess->app_uri);

                    $this->view = View::factory($this->viewPath . '/user.phtml', $user->getData());
                    $this->view->set('i18n', $this->i18n);
                    $this->send();
                } else {
                    $user->set('form', $form);
                    $this->view = View::factory($this->viewPath . '/user.phtml', $user->getData());
                    $this->view->set('i18n', $this->i18n);
                    $this->send();
                }
            } else {
                $user->set('form', $form);
                $this->view = View::factory($this->viewPath . '/user.phtml', $user->getData());
                $this->view->set('i18n', $this->i18n);
                $this->send();
            }
        }
    }

    /**
     * Error method
     *
     * @return void
     */
    public function error()
    {
        $install = new Model\Install(array(
            'title' => $this->i18n->__('404 Error') . ' &gt; ' . $this->i18n->__('Page Not Found')
        ));

        $this->view = View::factory($this->viewPath . '/error.phtml', $install->getData());
        $this->view->set('i18n', $this->i18n);
        $this->send(404);
    }

}

