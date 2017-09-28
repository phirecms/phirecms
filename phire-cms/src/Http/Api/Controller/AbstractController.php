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
namespace Phire\Http\Api\Controller;

use Pop\Http\Response;

/**
 * Abstract API controller class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractController extends \Phire\Http\Controller\AbstractController
{

    /**
     * Send response
     *
     * @param  int    $code
     * @param  mixed  $body
     * @param  string $message
     * @param  array  $headers
     * @return void
     */
    public function send($code = 200, $body = null, $message = null, array $headers = null)
    {
        $this->application->trigger('app.send.pre', ['controller' => $this]);

        $this->response->setCode($code);

        if (null !== $message) {
            $this->response->setMessage($message);
        }

        $this->response->setHeaders($this->application->config['api_headers']);

        $responseBody = (!empty($body)) ? json_encode($body, JSON_PRETTY_PRINT) : '';

        $this->response->setBody($responseBody . PHP_EOL . PHP_EOL);

        $this->application->trigger('app.send.post', ['controller' => $this]);
        $this->response->send(null, $headers);
    }

    /**
     * Send OPTIONS response
     *
     * @param  int    $code
     * @param  string $message
     * @param  array  $headers
     * @return void
     */
    public function sendOptions($code = 200, $message = null, array $headers = null)
    {
        $this->send($code, '', $message, $headers);
    }

    /**
     * Custom error handler method
     *
     * @param  int    $code
     * @param  string $message
     * @return void
     */
    public function error($code = 404, $message = null)
    {
        if (null === $message) {
            $message = Response::getMessageFromCode($code);
        }

        $responseBody = json_encode(['code' => $code, 'message' => $message], JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;

        $this->response->setCode($code)
            ->setMessage($message)
            ->setHeaders($this->application->config['api_headers'])
            ->setBody($responseBody)
            ->sendAndExit();
    }

}