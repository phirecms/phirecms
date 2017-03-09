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
namespace Phire\Event;

use Phire\Model;
use Phire\Table;
use Pop\Application;
use Pop\Http\Response;

/**
 * Session event class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Session
{

    /**
     * Check for the user session
     *
     * @param  Application $application
     * @return void
     */
    public static function check(Application $application)
    {
        $sess      = $application->getService('session');
        $action    = $application->router()->getRouteMatch()->getAction();
        $route     = $application->router()->getRouteMatch()->getOriginalRoute();
        $isInstall = (substr($route, 0, strlen(APP_URI . '/install')) == APP_URI . '/install');

        // If session exists, and is a non-session URL
        if (isset($sess->user) && (($action == 'login') || ($action == 'forgot') || ($action == 'verify') || ($isInstall))) {
            Response::redirect(BASE_PATH . APP_URI . '/');
            exit();
        // If session does not exists, and is a session URL
        } else if (!isset($sess->user) && ($action != 'login') && ($action != 'forgot') && ($action != 'verify') &&
            (!$isInstall) && (substr($_SERVER['REQUEST_URI'], 0, strlen(APP_URI)) == APP_URI)) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
            exit();
        }
    }

}