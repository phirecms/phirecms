<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */

require_once __DIR__ . '/config.php';

// Require autoloader
$autoloader = include __DIR__ . APP_PATH . '/vendor/autoload.php';

// Create main app object, register the app module and run the app
try {
    $app = new Popcorn\Pop($autoloader, include __DIR__ . APP_PATH . '/config/app.http.php');
    $app->register(new Phire\Module());
    $app->run();
} catch (\Exception $exception) {
    $app = new Phire\Module();
    $app->webError($exception);
}
