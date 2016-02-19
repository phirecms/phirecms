<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Event;

use Pop\Application;
use Pop\Http\Response;

/**
 * Ssl Event class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.0
 */
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