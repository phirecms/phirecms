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
namespace Phire\Http\Api\Event;

use Pop\Application;

/**
 * Options API event class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Options
{
    /**
     * Check for and re-route OPTIONS requests
     *
     * @param  Application $application
     * @return void
     */
    public static function check(Application $application)
    {
        if (($application->router()->hasController()) && (null !== $application->router()->getController()->request()) &&
            ($application->router()->getController()->request()->isOptions())) {
            $application->router()->getController()->sendOptions();
            exit();
        }
    }

}