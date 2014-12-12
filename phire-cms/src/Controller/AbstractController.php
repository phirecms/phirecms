<?php

namespace Phire\Controller;

use Pop\Controller\Controller;
use Pop\Http\Request;
use Pop\Http\Response;
use Pop\Service\Locator;

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
    }

}