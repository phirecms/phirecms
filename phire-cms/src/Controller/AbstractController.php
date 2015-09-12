<?php

namespace Phire\Controller;

use Pop\Application;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\Service\Locator;
use Pop\View\View;

abstract class AbstractController extends \Pop\Controller\AbstractController
{

    /**
     * Application object
     * @var Application
     */
    protected $application = null;

    /**
     * Services locator
     * @var Locator
     */
    protected $services = null;

    /**
     * Session object
     * @var \Pop\Web\Session
     */
    protected $sess = null;

    /**
     * Request object
     * @var \Pop\Http\Request
     */
    protected $request = null;

    /**
     * Response object
     * @var \Pop\Http\Response
     */
    protected $response = null;

    /**
     * View path
     * @var string
     */
    protected $viewPath = null;

    /**
     * View object
     * @var \Pop\View\View
     */
    protected $view = null;

    /**
     * Config object
     * @var \ArrayObject
     */
    protected $config = null;

    /**
     * Constructor for the controller
     *
     * @param  Application $application
     * @param  Request     $request
     * @param  Response    $response
     * @return AbstractController
     */
    public function __construct(Application $application, Request $request, Response $response)
    {
        $this->application = $application;
        $this->services    = $application->services();
        $this->request     = $request;
        $this->response    = $response;
        $this->sess        = $this->services['session'];
        $this->viewPath    = __DIR__ . '/../../view';

        if ($this->services->isAvailable('database')) {
            $this->config = (new \Phire\Model\Config())->getAll();
        }
    }

    /**
     * Get application object
     *
     * @return Application
     */
    public function application()
    {
        return $this->application;
    }

    /**
     * Get services object
     *
     * @return Locator
     */
    public function services()
    {
        return $this->services;
    }

    /**
     * Get request object
     *
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Get response object
     *
     * @return Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Get view object
     *
     * @return View
     */
    public function view()
    {
        return $this->view;
    }

    /**
     * Get config object
     *
     * @return \ArrayObject
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Determine if the view object has been created
     *
     * @return boolean
     */
    public function hasView()
    {
        return (null !== $this->view);
    }

    /**
     * Default error action method
     *
     * @return void
     */
    public function error()
    {
        $this->prepareView('phire/error.phtml');
        $this->view->title = 'Error';
        $this->send(404);
    }

    /**
     * Send response
     *
     * @param  int    $code
     * @param  array  $headers
     * @param  string $body
     * @return void
     */
    public function send($code = 200, array $headers = null, $body = null)
    {
        $this->response->setCode($code);
        $this->application->trigger('app.send.pre', ['controller' => $this]);

        if (null !== $body) {
            $this->response->setBody($body);
        } else if (null !== $this->view) {
            $this->response->setBody($this->view->render());
        }

        $this->application->trigger('app.send.post', ['controller' => $this]);

        $this->response->send($code, $headers);
    }

    /**
     * Redirect response
     *
     * @param  string $url
     * @param  string $code
     * @param  string $version
     * @return void
     */
    public function redirect($url, $code = '302', $version = '1.1')
    {
        $this->application->trigger('app.send.pre', ['controller' => $this]);
        $this->application->trigger('app.send.post', ['controller' => $this]);
        Response::redirect($url, $code, $version);
        exit();
    }

    /**
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        // Check for any override templates
        $headerTemplate = (file_exists($_SERVER['DOCUMENT_ROOT'] . MODULES_PATH . '/phire/view/phire/header.phtml')) ?
            $_SERVER['DOCUMENT_ROOT'] . MODULES_PATH . '/phire/view/phire/header.phtml' : __DIR__ . '/../../view/phire/header.phtml';

        $footerTemplate = (file_exists($_SERVER['DOCUMENT_ROOT'] . MODULES_PATH . '/phire/view/phire/footer.phtml')) ?
            $_SERVER['DOCUMENT_ROOT'] . MODULES_PATH . '/phire/view/phire/footer.phtml' : __DIR__ . '/../../view/phire/footer.phtml';

        $viewTemplate = (file_exists($_SERVER['DOCUMENT_ROOT'] . MODULES_PATH . '/phire/view/' . $template)) ?
            $_SERVER['DOCUMENT_ROOT'] . MODULES_PATH . '/phire/view/' . $template : $this->viewPath . '/' . $template;

        $this->view                  = new View($viewTemplate);
        $this->view->phire           = new \ArrayObject([], \ArrayObject::ARRAY_AS_PROPS);
        $this->view->assets          = $this->application->module('phire')->getAssets();
        $this->view->systemTitle     = $this->application->config()['system_title'];
        $this->view->phireHeader     = $headerTemplate;
        $this->view->phireFooter     = $footerTemplate;
        $this->view->phireUri        = BASE_PATH . APP_URI;
        $this->view->basePath        = BASE_PATH;
        $this->view->base_path       = BASE_PATH;
        $this->view->contentPath     = BASE_PATH . CONTENT_PATH;
        $this->view->content_path    = BASE_PATH . CONTENT_PATH;

        if (isset($this->sess->installed)) {
            $this->view->installed = true;
        }
        if (isset($this->sess->saved)) {
            $this->view->saved = true;
        }
        if (isset($this->sess->removed)) {
            $this->view->removed = true;
        }

        if (isset($this->sess->user)) {
            $this->services['nav.phire']->setRole($this->services['acl']->getRole($this->sess->user->role));
            $this->services['nav.phire']->returnFalse(true);
            $this->view->phireNav  = $this->services['nav.phire'];
            $this->view->phirePath = BASE_PATH . APP_PATH;
            $this->view->docRoot   = $_SERVER['DOCUMENT_ROOT'];
            $this->view->user      = $this->sess->user;
            $this->view->acl       = $this->services['acl'];
            $this->view->config    = $this->config;
        } else {
            $this->view->phireNav = null;
        }
    }

}