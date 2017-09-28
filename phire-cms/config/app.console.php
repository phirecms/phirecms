<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */

/**
 * Phire CMS Console Configuration File
 */
return [
    'routes'   => include 'routes/console.php',
    'database' => [
        'adapter'  => DB_ADAPTER,
        'database' => DB_NAME,
        'username' => DB_USER,
        'password' => DB_PASS,
        'host'     => DB_HOST,
        'type'     => DB_TYPE
    ]
];