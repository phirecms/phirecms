<?php
/**
 * Phire CMS Application Configuration File
 */

// Set maintenance mode
define('MAINTENANCE', false);

/**
 * URI and Path Configuration Settings
 */

// Define the application URI
define('APP_URI', '/phire');

// Define the content path
define('CONTENT_PATH', '/phire-content');

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
define('DB_PREFIX', 'ph_');