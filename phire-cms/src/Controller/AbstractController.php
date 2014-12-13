<?php

namespace Phire\Controller;

use Pop\Controller\Controller;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\Service\Locator;
use Pop\View\View;

class AbstractController extends Controller
{
    /**
     * Service locator
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
     * Constructor for the controller
     *
     * @param Locator $services
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Locator $services, Request $request, Response $response)
    {
        $this->services = $services;
        $this->request  = $request;
        $this->response = $response;
        $this->sess     = $this->services['session'];
        $this->viewPath = __DIR__ . '/../../view';
    }

    public function error()
    {
        $view = new View($this->viewPath . '/error.phtml');
        $view->title = 'Error';

        $this->response->setBody($view->render());
        $this->send(404);
    }

    /**
     * Send response
     *
     * @param  int   $code
     * @param  array $headers
     * @return void
     */
    public function send($code = null, array $headers = null)
    {
        $this->response->send($code, $headers);
    }

}