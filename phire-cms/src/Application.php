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
        if ($this->services->isAvailable('database')) {
            Record::setDb($this->getService('database'));
        }

        if (null !== $this->router) {
            $this->router->addRouteParams(
                '*', [
                    'services' => $this->services,
                    'request'  => new Request(),
                    'response' => new Response()
                ]
            );
        }

        return parent::init();
    }

}