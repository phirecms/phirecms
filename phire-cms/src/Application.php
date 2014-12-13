<?php

namespace Phire;

use Pop\Db\Record;
use Pop\Http\Request;
use Pop\Http\Response;

class Application extends \Pop\Application
{

    /**
     * Phire version
     */
    const VERSION = '2.0.0b';

    public function init()
    {
        // Set the database
        if ($this->services->isAvailable('database')) {
            Record::setDb($this->getService('database'));
        }

        // Add route params for the controllers
        if (null !== $this->router) {
            $this->router->addRouteParams(
                '*', [
                    'services' => $this->services,
                    'request'  => new Request(),
                    'response' => new Response()
                ]
            );
        }

        // Session check
        $this->on('app.dispatch.pre', function(Application $application){
            $sess   = $application->getService('session');
            $action = $application->router()->getRouteMatch()->getAction();

            if (isset($sess->user) && (($action == 'login') || ($action == 'register'))) {
                Response::redirect(BASE_PATH . APP_URI);
                exit();
            } else if (!isset($sess->user) && (($action != 'login') && ($action != 'register') &&
                    ($action != 'unsubscribe') && (null !== $action))) {
                Response::redirect(BASE_PATH . APP_URI . '/login');
                exit();
            }
        });

        return parent::init();
    }

}