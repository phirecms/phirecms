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

use Pop\Application;
use Pop\Http\Response;

/**
 * Acl event class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Acl
{

    /**
     * Check if the user session is allowed with the ACL service
     *
     * @param  Application $application
     * @return void
     */
    public static function check(Application $application)
    {
        if ($application->services()->isAvailable('database')) {
            $application->module('phire')->initAcl();
            $sess = $application->getService('session');
            $acl  = $application->getService('acl');

            if (isset($sess->user) && isset($sess->user->role) && ($acl->hasRole($sess->user->role))) {
                $route  = $application->router()->getRouteMatch()->getOriginalRoute();
                $routes = $application->router()->getRouteMatch()->getFlattenedRoutes();

                if (isset($routes[$route]) && isset($routes[$route]['acl']) &&
                    isset($routes[$route]['acl']['resource'])) {
                    $resource   = $routes[$route]['acl']['resource'];
                    $permission = (isset($routes[$route]['acl']['permission'])) ?
                        $routes[$route]['acl']['permission'] : null;
                    if (!$acl->isAllowed($sess->user->role, $resource, $permission)) {
                        Response::redirect(BASE_PATH . APP_URI . '/');
                        exit();
                    }
                }
            }
        }
    }

}