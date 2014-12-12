<?php

namespace Phire;

use Pop\Http\Request;
use Pop\Http\Response;

class Application extends \Pop\Application
{

    public function init()
    {
        if ($this->services->isAvailable('database')) {
            \Pop\Db\Record::setDb($this->getService('database'));
        }

        if (null !== $this->router) {
            $this->router->addRouteParams(
                'Phire\Controller\Phire\IndexController', [
                    'services' => $this->services,
                    'request'  => new Request(),
                    'response' => new Response()
                ]
            );
        }

        return parent::init();
    }

}