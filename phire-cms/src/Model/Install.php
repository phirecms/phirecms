<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;

/**
 * Install model class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Install extends AbstractModel
{

    /**
     * Get available DB adapters
     *
     * @return array
     */
    public function getDbAdapters()
    {
        $dbAdapters = [];
        $pdoDrivers = (class_exists('Pdo', false)) ? \PDO::getAvailableDrivers() : [];

        if (class_exists('mysqli', false)) {
            $dbAdapters['mysql'] = 'Mysql';
        }
        if (function_exists('pg_connect')) {
            $dbAdapters['pgsql'] = 'PostgreSQL';
        }
        if (class_exists('Sqlite3', false)) {
            $dbAdapters['sqlite'] = 'SQLite';
        }
        if (in_array('mysql', $pdoDrivers)) {
            $dbAdapters['pdo_mysql'] = 'PDO\Mysql';
        }
        if (in_array('pgsql', $pdoDrivers)) {
            $dbAdapters['pdo_pgsql'] = 'PDO\PostgreSQL';
        }
        if (in_array('sqlite', $pdoDrivers)) {
            $dbAdapters['pdo_sqlite'] = 'PDO\SQLite';
        }

        return $dbAdapters;
    }

}