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
namespace Phire\Http\Web\Controller;

use Pop\Http\Response;
use Pop\View\View;

/**
 * Abstract HTTP controller class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0-alpha
 */
abstract class AbstractController extends \Phire\Http\Controller\AbstractController
{


    /**
     * View path
     * @var string
     */
    protected $viewPath = __DIR__ . '/../../../../view';

    /**
     * View object
     * @var \Pop\View\View
     */
    protected $view = null;

    /**
     * Get view object
     *
     * @return View
     */
    public function getView()
    {
        return $this->view;
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
     * Send response
     *
     * @param  string $body
     * @param  int    $code
     * @param  string $message
     * @param  array  $headers
     * @return void
     */
    public function send($body = null, $code = 200, $message = null, array $headers = null)
    {
        $this->application->trigger('app.send.pre', ['controller' => $this]);

        if ((null === $body) && (null !== $this->view)) {
            $body = $this->view->render();
        }

        if (null !== $message) {
            $this->response->setMessage($message);
        }

        $this->response->setCode($code);

        if (null === $this->response->getHeader('Content-Type')) {
            $this->response->setHeader('Content-Type', 'text/html');
        }

        $this->response->setBody($body . PHP_EOL . PHP_EOL);

        $this->application->trigger('app.send.post', ['controller' => $this]);

        $this->response->send(null, $headers);
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
     * Prepare view
     *
     * @param  string $template
     * @return void
     */
    protected function prepareView($template)
    {
        $this->view = new View($this->viewPath . '/' . $template);
    }

}