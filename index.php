<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

require_once __DIR__ . '/config.php';

try {
    // Check the app constants
    if (!defined('BASE_PATH') || !defined('APP_PATH') || !defined('APP_URI') ||
        !defined('DB_INTERFACE') || !defined('DB_NAME')) {
        throw new \Exception(
            'Error: The config file is not properly configured. Please check the config file or install the system.'
        );
    }

    // Get the autoloader
    $autoloader = require __DIR__ . APP_PATH . '/vendor/autoload.php';

    // Create main app object
    $app = new Pop\Application(
        $autoloader,
        include __DIR__ . APP_PATH . '/config/application.php'
    );

    // Register the main Phire module, run the app
    $app->register('phire', new Phire\Module($app))
        ->run();
} catch (Exception $exception) {
    $phire = new Phire\Module();
    $phire->error($exception);
}
