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
namespace Phire\Http\Event;

use Pop\Application;
use Pop\Http\Response;
use Pop\View\View;

/**
 * Maintenance event class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0-alpha
 */
class Maintenance
{

    /**
     * Check if maintenance mode is set
     *
     * @param  Application $application
     * @return void
     */
    public static function check(Application $application)
    {
        if ((defined('MAINTENANCE')) && (MAINTENANCE)) {
            if ($application->modules['phire-cms']->isWeb()) {
                $acceptHeader = $application->router()->getController()->request()->getHeader('Accept');
                if (($acceptHeader != '*/*') && (stripos($acceptHeader, 'text/html') === false)) {
                    $controller = new \Phire\Http\Api\Controller\IndexController(
                        $application, $application->router()->getController()->request(), $application->router()->getController()->response()
                    );
                    $controller->error(406);
                } else {
                    $view = new View(__DIR__ . '/../../../view/maintenance.phtml');
                    $view->title = 'Maintenance';
                    $response = new Response();
                    $response->setCode(503);
                    $response->setBody($view->render());
                    $response->sendAndExit();
                }
            } else {
                $application->router()->getController()->error(503);
            }
        }
    }

}