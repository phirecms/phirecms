<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Controller;

use Pop\Application;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\Service\Locator;
use Pop\View\View;

/**
 * Abstract controller class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class AbstractController extends \Pop\Controller\AbstractController
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
     * @var \Pop\Session\Session
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
     */
    public function __construct(Application $application, Request $request, Response $response)
    {
        $this->application = $application;
        $this->services    = $application->services();
        $this->request     = $request;
        $this->response    = $response;
        $this->sess        = $this->services['session'];
        $this->viewPath    = __DIR__ . '/../../view';
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
        $this->prepareView('error.phtml');
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
        Response::redirect($url, $code, $version);
        exit();
    }

    /**
     * Get query string
     *
     * @param  mixed  $omit
     * @return string
     */
    public function getQueryString($omit = null)
    {
        if ((null !== $omit) && !is_array($omit)) {
            $omit = [$omit];
        }

        $get   = $this->request->getQuery();
        $query = [];
        foreach ($get as $key => $value) {
            if (!isset($query[$key]) && !in_array($key, $omit)) {
                $query[$key] = $value;
            }
        }

        return (count($query) > 0) ? http_build_query($query) : '';
    }

    /**
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->view = new View($this->viewPath . '/' . $template);

        $this->view->application_title = $this->application->config()['application_title'];
        $this->view->phireVersion      = \Phire\Module::VERSION;

        if (isset($this->sess->installed)) {
            $this->view->installed = true;
        }

        if (isset($this->sess->failed)) {
            $this->view->failed = true;
        }

        if (isset($this->sess->expired)) {
            $this->view->expired = true;
        }

        if (isset($this->sess->saved)) {
            $this->view->saved = true;
        }

        if (isset($this->sess->removed)) {
            $this->view->removed = true;
        }

        if (isset($this->sess->user)) {
            $this->services['nav.top']->setRole($this->services['acl']->getRole($this->sess->user->role));
            $this->services['nav.top']->returnFalse(true);

            if ($this->services->isAvailable('nav.side')) {
                $this->services['nav.side']->setRole($this->services['acl']->getRole($this->sess->user->role));
                $this->services['nav.side']->returnFalse(true);
                if (count($this->services['nav.side']->getTree()) > 0) {
                    $this->view->sideNav = $this->services['nav.side'];
                }
            }

            $this->view->phireNav      = $this->services['nav.top'];
            $this->view->acl           = $this->services['acl'];
            $this->view->dashboard     = $this->application->config()['dashboard'];
            $this->view->dashboardSide = $this->application->config()['dashboard_side'];
            $this->view->user          = $this->sess->user;
        }
    }

}