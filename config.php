<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link        https://github.com/phirecms/phirecms
 * @author      Nick Sagona, III <dev@nolainteractive.com>
 * @copyright   Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license     http://www.phirecms.org/license     New BSD License
 */

/**
 * Phire CMS 2 Configuration File
 */

/**
 * Path and URI Configuration Settings
 */
// Calculate and define the base path
$basePath = str_replace([realpath($_SERVER['DOCUMENT_ROOT']), '\\'], ['', '/'], realpath(__DIR__));
define('BASE_PATH', (!empty($basePath) ? $basePath : ''));

// Define the application path
define('APP_PATH', '/phire-cms');

// Define the content path
define('CONTENT_PATH', '/phire-content');

// Define the modules path
define('MODULES_PATH', BASE_PATH . CONTENT_PATH . '/modules');

// Define the application URI
define('APP_URI', '/phire');

// Define absolute paths
define('BASE_ABS_PATH', realpath(__DIR__ . BASE_PATH));
define('APP_ABS_PATH', realpath(__DIR__ . APP_PATH));
define('CONTENT_ABS_PATH', realpath(__DIR__ . CONTENT_PATH));
define('MODULES_ABS_PATH', realpath(__DIR__ . MODULES_PATH));

/**
 * Database Configuration Settings
 */
// Define the database interface: 'mysql', 'pgsql', 'sqlite' or 'pdo'
define('DB_INTERFACE', '');

// Define the database DSN type (for Pdo only): 'mysql', 'pgsql' or 'sqlite'
define('DB_TYPE', '');

// Define the database name
define('DB_NAME', '');

// Define the database user
define('DB_USER', '');

// Define the database password
define('DB_PASS', '');

// Define the database host
define('DB_HOST', '');

// Define the database prefix
define('DB_PREFIX', '');

/**
 * Custom Application Configuration Settings
 */
