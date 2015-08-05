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
            $forceSsl = $application->config()['force_ssl'];
            // If force_ssl is checked, and request is not secure, redirect to secure request
            if (($forceSsl) && ($_SERVER['SERVER_PORT'] != '443')) {
                $secureUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] .
                    ((!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '');
                Response::redirect($secureUrl);
                exit();
            }
        }
    }

}