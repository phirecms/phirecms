<?php
/**
 * Phire CMS 2.0 Bootstrap File
 */

// Calculate and define the base path
if (!defined('BASE_PATH')) {
    $basePath = str_replace(array(realpath($_SERVER['DOCUMENT_ROOT']), '\\'), array('', '/'), realpath(__DIR__));
    define('BASE_PATH', (!empty($basePath) ? $basePath : ''));
}

// Require the config file
require_once 'config.php';

// Check the path and URI constants
if (!defined('BASE_PATH') || !defined('APP_PATH') || !defined('APP_URI') ||
    !defined('DB_INTERFACE') || !defined('DB_NAME')) {
    throw new \Exception('Error: The config file is not properly configured. Please check the config file or install the system.');
}

// Require the Pop Autoloader class file
require_once __DIR__ . APP_PATH . '/vendor/PopPHPFramework/src/Pop/Loader/Autoloader.php';

// Create the autoloader object and register the Phire application
$autoloader = new \Pop\Loader\Autoloader();
$autoloader->splAutoloadRegister(false);
$autoloader->register('Phire', __DIR__ . APP_PATH . '/vendor/Phire/src');

// Create the Phire project object
$project = \Phire\Project::factory(
    include __DIR__ . APP_PATH . '/config/project.php',
    include __DIR__ . APP_PATH . '/vendor/Phire/config/module.php'
);
