<?php

namespace Phire\Event;

use Pop\Application;
use Pop\Http\Response;

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
        if ($application->config()['db']) {
            $application->module('Phire')->initAcl();
            $sess = $application->getService('session');
            $acl  = $application->getService('acl');

            if (isset($sess->user) && isset($sess->user->role) && ($acl->hasRole($sess->user->role))) {
                // Get routes with slash options
                $route  = $application->router()->getRouteMatch()->getRoute();
                $routes = $application->router()->getRouteMatch()->getRoutes();
                if (isset($routes[$route]) && isset($routes[$route]['acl']) &&
                    isset($routes[$route]['acl']['resource'])) {
                    $resource   = $routes[$route]['acl']['resource'];
                    $permission = (isset($routes[$route]['acl']['permission'])) ?
                        $routes[$route]['acl']['permission'] : null;
                    if (!$acl->isAllowed($sess->user->role, $resource, $permission)) {
                        Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
                        exit();
                    }
                }
            }
        }
    }

}