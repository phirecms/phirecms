<?php

namespace Phire\Event;

use Pop\Application;
use Pop\Http\Response;

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
        $route     = $application->router()->getRouteMatch()->getRoute();
        $isInstall = (substr($route, 0, strlen(APP_URI . '/install')) == APP_URI . '/install');

        // Special install check
        if (isset($sess->app_uri) && (strpos($_SERVER['REQUEST_URI'], 'install/config') !== false)) {
            if ((BASE_PATH . APP_URI) == (BASE_PATH . $sess->app_uri) && ($application->config()['db'])) {
                Response::redirect(BASE_PATH . APP_URI . '/install/user');
                exit();
            }
        }

        // If logged in, and a system URL, redirect to dashboard
        if (isset($sess->user) && (($action == 'login') || ($action == 'register') ||
                ($action == 'verify') || ($action == 'forgot') || ($isInstall))) {
            Response::redirect(BASE_PATH . ((APP_URI != '') ? APP_URI : '/'));
            exit();
            // Else, if NOT logged in and NOT a system URL, redirect to login
        } else if (!isset($sess->user) && (($action != 'login') && ($action != 'register') && (!$isInstall) &&
                ($action != 'unsubscribe') && ($action != 'verify') && ($action != 'forgot') && (null !== $action)) &&
            (substr($route, 0, strlen(APP_URI)) == APP_URI)) {
            Response::redirect(BASE_PATH . APP_URI . '/login');
            exit();
        }
    }

}