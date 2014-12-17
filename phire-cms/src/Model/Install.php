<?php

namespace Phire\Model;

use Phire\Table;
use Pop\Db\Db;

class Install extends AbstractModel
{

    /**
     * Install DB
     *
     * @param  array $fields
     * @return void
     */
    public function installDb(array $fields)
    {
        if (stripos($fields['db_adapter'], 'pdo_') !== false) {
            $dbAdapter = 'Pdo';
            $dbType    = substr($fields['db_adapter'], 4);
            $sql       = __DIR__ . '/../../data/phire.' . strtolower($dbType) . '.sql';
        } else {
            $dbAdapter = ucfirst(strtolower($fields['db_adapter']));
            $dbType    = null;
            $sql       = __DIR__ . '/../../data/phire.' . strtolower($fields['db_adapter']) . '.sql';
        }

        if (stripos($fields['db_adapter'], 'sqlite') !== false) {
            touch(__DIR__ . '/../../..' . CONTENT_PATH . '/.htphire.sqlite');
            chmod(__DIR__ . '/../../..' . CONTENT_PATH . '/.htphire.sqlite', 0777);
            $database = __DIR__ . '/../../..' . CONTENT_PATH . '/.htphire.sqlite';
        } else {
            $database = $fields['db_name'];
        }

        Db::install($sql, [
            'database' => $database,
            'username' => $fields['db_username'],
            'password' => $fields['db_password'],
            'host'     => $fields['db_host'],
            'prefix'   => $fields['db_prefix'],
            'type'     => $dbType
        ], $dbAdapter);
    }

    /**
     * Create the config
     *
     * @param  array $fields
     * @return string
     */
    public function createConfig(array $fields)
    {
        $dbName = (stripos($fields['db_adapter'], 'sqlite') === false) ?
            "'" . $fields['db_name'] . "'" : "__DIR__ .  CONTENT_PATH . '/.htphire.sqlite'";

        if (stripos($fields['db_adapter'], 'pdo_') !== false) {
            $dbAdapter = 'pdo';
            $dbType    = substr($fields['db_adapter'], 4);
        } else {
            $dbAdapter = $fields['db_adapter'];
            $dbType    = null;
        }

        $config = file_get_contents(__DIR__ . '/../../data/config.orig.php');
        $config = str_replace(
            [
                "define('CONTENT_PATH', '/phire-content');",
                "define('APP_URI', '/phire');",
                "define('DB_INTERFACE', '');",
                "define('DB_TYPE', '');",
                "define('DB_NAME', '');",
                "define('DB_USER', '');",
                "define('DB_PASS', '');",
                "define('DB_HOST', '');",
                "define('DB_PREFIX', '');"
            ],
            [
                "define('CONTENT_PATH', '" . $fields['content_path'] . "');",
                "define('APP_URI', '" . $fields['app_uri'] . "');",
                "define('DB_INTERFACE', '" . $dbAdapter . "');",
                "define('DB_TYPE', '" . $dbType . "');",
                "define('DB_NAME', " . $dbName . ");",
                "define('DB_USER', '" . $fields['db_username'] . "');",
                "define('DB_PASS', '" . $fields['db_password'] . "');",
                "define('DB_HOST', '" . $fields['db_host'] . "');",
                "define('DB_PREFIX', '" . $fields['db_prefix'] . "');"
            ], $config);

        return htmlentities($config, ENT_QUOTES, 'UTF-8');
    }

}