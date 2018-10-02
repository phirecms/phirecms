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
namespace Phire\Http\Api\Event;

use Pop\Application;
use Pop\Http\Response;

/**
 * Options event class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0-alpha
 */
class Options
{

    /**
     * Send Options
     *
     * @param  Application $application
     * @return void
     */
    public static function check(Application $application)
    {
        if (($application->router()->getController()->request()->isOptions()) &&
            ($application->router()->hasController()) && (null !== $application->router()->getController()->request())) {
            if (method_exists($application->router()->getController(), 'sendOptions')) {
                $application->router()->getController()->sendOptions();
                exit();
            } else {
                $response = new Response();
                foreach ($application->config['http_options_headers'] as $header => $value) {
                    $response->setHeader($header, $value);
                }
                $response->setCode(400);
                $response->setBody(json_encode(['code' => 400, 'message' => 'Bad Request'], JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL);
                $response->sendAndExit();
            }
        }
    }

}