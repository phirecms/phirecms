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

use Pop\Db\Db;
use Pop\Http\Client\Curl;

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

    /**
     * Install DB
     *
     * @param  mixed $form
     * @return void
     */
    public function installDb($form)
    {
        if (stripos($form['db_adapter'], 'pdo_') !== false) {
            $dbAdapter = 'Pdo';
            $dbType    = substr($form['db_adapter'], 4);
            $sql       = __DIR__ . '/../../database/phire.' . strtolower($dbType) . '.sql';
        } else {
            $dbAdapter = ucfirst(strtolower($form['db_adapter']));
            $dbType    = null;
            $sql       = __DIR__ . '/../../database/phire.' . strtolower($form['db_adapter']) . '.sql';
        }

        if (stripos($form['db_adapter'], 'sqlite') !== false) {
            touch(__DIR__ . '/../../..' . CONTENT_PATH . '/.htphire.sqlite');
            chmod(__DIR__ . '/../../..' . CONTENT_PATH . '/.htphire.sqlite', 0777);
            $database = __DIR__ . '/../../..' . CONTENT_PATH . '/.htphire.sqlite';
        } else {
            $database = $form['db_name'];
        }

        Db::install($sql, $dbAdapter, [
            'database' => $database,
            'username' => $form['db_username'],
            'password' => $form['db_password'],
            'host'     => $form['db_host'],
            'prefix'   => $form['db_prefix'],
            'type'     => $dbType
        ]);
    }

    /**
     * Create the config
     *
     * @param  mixed $form
     * @return string
     */
    public function createConfig($form)
    {
        $dbName = (stripos($form['db_adapter'], 'sqlite') === false) ?
            "'" . $form['db_name'] . "'" : "__DIR__ .  CONTENT_PATH . '/.htphire.sqlite'";

        if (stripos($form['db_adapter'], 'pdo_') !== false) {
            $dbAdapter = 'pdo';
            $dbType    = substr($form['db_adapter'], 4);
        } else {
            $dbAdapter = $form['db_adapter'];
            $dbType    = null;
        }

        $config = file_get_contents(__DIR__ . '/../../config/config.orig.php');
        $config = str_replace(
            [
                "define('CONTENT_PATH', '/phire-content');",
                "define('APP_URI', '/phire');",
                "define('DB_ADAPTER', '');",
                "define('DB_TYPE', '');",
                "define('DB_NAME', '');",
                "define('DB_USER', '');",
                "define('DB_PASS', '');",
                "define('DB_HOST', '');",
                "define('DB_PREFIX', '');"
            ],
            [
                "define('CONTENT_PATH', '" . $form['content_path'] . "');",
                "define('APP_URI', '" . ((!empty($form['app_uri']) && ($form['app_uri'] != '/')) ?
                    $form['app_uri'] : null) . "');",
                "define('DB_ADAPTER', '" . $dbAdapter . "');",
                "define('DB_TYPE', '" . $dbType . "');",
                "define('DB_NAME', " . $dbName . ");",
                "define('DB_USER', '" . $form['db_username'] . "');",
                "define('DB_PASS', '" . $form['db_password'] . "');",
                "define('DB_HOST', '" . $form['db_host'] . "');",
                "define('DB_PREFIX', '" . $form['db_prefix'] . "');"
            ], $config);

        return $config;
    }

    /**
     * Send installation stats
     *
     * @return void
     */
    public function sendStats()
    {
        $headers = [
            'Authorization: ' . base64_encode('phire-stats-' . time()),
            'User-Agent: ' . (isset($_SERVER['HTTP_USER_AGENT']) ?
                $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox/41.0')
        ];
        $curl = new Curl('http://stats.phirecms.org/system', [
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $curl->setPost();
        $curl->setFields([
            'version'   => \Phire\Module::VERSION,
            'domain'    => (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
            'ip'        => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
            'os'        => PHP_OS,
            'server'    => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : ''),
            'php'       => PHP_VERSION,
            'db'        => DB_ADAPTER . ((DB_ADAPTER == 'pdo') ? ' (' . DB_TYPE . ')' : '')
        ]);
        $curl->send();
    }

}