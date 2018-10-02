<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Http\Event;

use Pop\Application;
use Pop\Http\Response;

/**
 * Auth event class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0-alpha
 */
class Auth
{

    /**
     * Public actions
     */
    protected static $publicActions = [
        'Phire\Http\Api\Controller\IndexController' => [
            'authenticate'
        ],
        'Phire\Http\Web\Controller\IndexController' => [
            'login'
        ]
    ];

    /**
     * Check user auth
     *
     * @param  Application $application
     * @return void
     */
    public static function authenticate(Application $application)
    {
        $ctrl   = $application->router()->getControllerClass();
        $action = $application->router()->getRouteMatch()->getAction();

        if (!self::isPublicAction($ctrl, $action)) {
            if ($application->modules['phire-cms']->isApi()) {
                $authToken = $application->router()->getController()->request()->getHeader('Authorization');
                // Validate token
            } else {
                // Validate user
                $sess = $application->services['session'];
                if (!isset($sess->user)) {
                    Response::redirect(APP_URI . '/login');
                    exit();
                }
            }
        }
    }

    /**
     * Check if public action
     *
     * @param  string $controller
     * @param  string $action
     * @return boolean
     */
    public static function isPublicAction($controller, $action)
    {
        return (isset(self::$publicActions[$controller]) && in_array($action, self::$publicActions[$controller]));
    }

}