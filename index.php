<?php

require_once __DIR__  . '/config.php';

try {
    // Check the app constants
    if (!defined('BASE_PATH') || !defined('APP_PATH') || !defined('APP_URI') ||
        !defined('DB_INTERFACE') || !defined('DB_NAME')) {
        throw new \Exception(
            'Error: The config file is not properly configured. Please check the config file or install the system.'
        );
    }

    // Get the autoloader
    $autoloader = require __DIR__  . APP_PATH . '/vendor/autoload.php';

    // Create and run the app
    $app = new Phire\Application(
        $autoloader,
        include __DIR__ . APP_PATH . '/config/application.php'
    );
    $app->run();
} catch (Exception $exception) {
    echo $exception->getMessage();
}
