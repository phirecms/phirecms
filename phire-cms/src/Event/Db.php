<?php

namespace Phire\Event;

use Pop\Application;

class Db
{

    /**
     * Check if the database has been installed and a database connection is available
     *
     * @param  Application $application
     * @throws \Phire\Exception
     * @return void
     */
    public static function check(Application $application)
    {
        $route = $application->router()->getRouteMatch()->getRoute();
        if (!$application->config()['db'] &&
            (substr($route, 0, strlen(APP_URI . '/install')) != APP_URI . '/install')) {
            $exception = new \Phire\Exception(
                'Error: The database has not been installed. ' .
                'Please check the config file or <a href="' . BASE_PATH . APP_URI . '/install">install</a> the system.'
            );
            $exception->setInstallErrorFlag(true);
            throw $exception;
        }
    }

}