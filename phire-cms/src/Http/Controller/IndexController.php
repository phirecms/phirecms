<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Http\Controller;

use Pop\View\View;

/**
 * Index controller class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class IndexController extends AbstractController
{

    /**
     * Error handler method
     *
     * @return void
     */
    public function error()
    {
        $response = ['code' => 404, 'message' => 'Not Found'];

        if (stripos($this->request->getHeader('Accept'), 'text/html') !== false) {
            $view = new View(__DIR__ . '/../../view/error.phtml', $response);
            $view->title = 'Error: ' .  $response['code'] . ' ' . $response['message'];
            if ($this->application->services->isLoaded('session')) {
                $sess = $this->application->services['session'];
                $view->username = $sess->user->username;
            }
            $this->response->setHeader('Content-Type', 'text/html');
            $this->response->setBody($view->render());
        } else {
            $this->response->setHeader('Content-Type', 'application/json');
            $this->response->setBody(json_encode($response, JSON_PRETTY_PRINT) . PHP_EOL);
        }

        $this->response->send(404);
        exit();
    }

}