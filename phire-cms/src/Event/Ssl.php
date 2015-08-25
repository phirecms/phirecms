<?php

namespace Phire\Event;

use Pop\Application;
use Pop\Http\Response;

class Ssl
{

    /**
     * Check if the application requires an SSL connection
     *
     * @param  Application $application
     * @return void
     */
    public static function check(Application $application)
    {
        if ($application->config()['db']) {
            // If force_ssl is checked, and request is not secure, redirect to secure request
            if (($application->config()['force_ssl']) && ($_SERVER['SERVER_PORT'] != '443') &&
                (substr($_SERVER['REQUEST_URI'], 0, strlen(BASE_PATH . APP_URI)) == (BASE_PATH . APP_URI))) {
                Response::redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                exit();
            }
        }
    }

}