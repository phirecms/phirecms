<?php

namespace Phire\Controller;

use Phire\Application;
use Pop\Controller\Controller;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\Service\Locator;
use Pop\View\View;

abstract class AbstractController extends Controller
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
     * Get request object
     *
     * @return \Pop\Http\Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Get response object
     *
     * @return \Pop\Http\Response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Get view object
     *
     * @return \Pop\View\View
     */
    public function view()
    {
        return $this->view;
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
    public function send($code = null, array $headers = null, $body = null)
    {
        $this->application->trigger('app.send', ['controller' => $this]);

        if (null !== $body) {
            $this->response->setBody($body);
        } else if (null !== $this->view) {
            $this->response->setBody($this->view->render());
        }

        $this->response->send($code, $headers);
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
        $headerTemplate = (file_exists(__DIR__ . '/../../..' . MODULE_PATH . '/phire/view/header.phtml')) ?
            __DIR__ . '/../../..' . MODULE_PATH . '/phire/view/header.phtml' : __DIR__ . '/../../view/header.phtml';

        $footerTemplate = (file_exists(__DIR__ . '/../../..' . MODULE_PATH . '/phire/view/footer.phtml')) ?
            __DIR__ . '/../../..' . MODULE_PATH . '/phire/view/footer.phtml' : __DIR__ . '/../../view/footer.phtml';

        $viewTemplate = (file_exists(__DIR__ . '/../../..' . MODULE_PATH . '/phire/view/' . $template)) ?
            __DIR__ . '/../../..' . MODULE_PATH . '/phire/view/' . $template : $this->viewPath . '/' . $template;

        $this->view              = new View($viewTemplate);
        $this->view->assets      = $this->application->getAssets();
        $this->view->phireHeader = $headerTemplate;
        $this->view->phireFooter = $footerTemplate;

        if (isset($this->sess->user)) {
            $this->services['nav.phire']->setRole($this->services['acl']->getRole($this->sess->user->role));
            $this->services['nav.phire']->returnFalse(true);
            $this->view->phireNav = $this->services['nav.phire'];
            $this->view->user     = $this->sess->user;
            $this->view->acl      = $this->services['acl'];
            $this->view->config   = $this->config;
        } else {
            $this->view->phireNav = null;
        }
    }

}