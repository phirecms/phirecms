<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;
use Pop\Db\Db;
use Pop\Mail\Mail;
use Pop\Http\Client\Curl;

/**
 * Install Model class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.1rc1
 */
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
                "define('APP_URI', '" . ((!empty($fields['app_uri']) && ($fields['app_uri'] != '/')) ?
                    $fields['app_uri'] : null) . "');",
                "define('DB_INTERFACE', '" . $dbAdapter . "');",
                "define('DB_TYPE', '" . $dbType . "');",
                "define('DB_NAME', " . $dbName . ");",
                "define('DB_USER', '" . $fields['db_username'] . "');",
                "define('DB_PASS', '" . $fields['db_password'] . "');",
                "define('DB_HOST', '" . $fields['db_host'] . "');",
                "define('DB_PREFIX', '" . $fields['db_prefix'] . "');"
            ], $config);

        return $config;
    }

    /**
     * Install Profile
     *
     * @param  string $sql
     * @return void
     */
    public function installProfile($sql)
    {
        Db::install($sql, [
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'host'     => DB_HOST,
            'prefix'   => DB_PREFIX,
            'type'     => DB_TYPE
        ], ucfirst(strtolower(DB_INTERFACE)));
    }

    /**
     * Send installation confirmation
     *
     * @param  Table\Users
     * @return void
     */
    public function sendConfirmation($user)
    {
        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);
        $schema = (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')) ? 'https://' : 'http://';

        // Set the recipient
        $rcpt = [
            'name'   => $user->username,
            'email'  => $user->email,
            'login'  => $schema . $_SERVER['HTTP_HOST'] . BASE_PATH . APP_URI . '/login',
            'domain' => $domain
        ];

        // Check for an override template
        $mailTemplate = (file_exists(MODULES_ABS_PATH . '/phire/view/phire/mail/install.txt')) ?
            MODULES_ABS_PATH . '/phire/view/phire/mail/install.txt' : __DIR__ . '/../../view/phire/mail/install.txt';

        // Send email verification
        $mail = new Mail($domain . ' - Phire CMS Installation', $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText(file_get_contents($mailTemplate));
        $mail->send();

        // Save domain
        $config = Table\Config::findById('domain');
        $config->value = $_SERVER['HTTP_HOST'];
        $config->save();

        // Save document root
        $config = Table\Config::findById('document_root');
        $config->value = $_SERVER['DOCUMENT_ROOT'];
        $config->save();

        // Save install timestamp
        $config = Table\Config::findById('installed_on');
        $config->value = (string)date('Y-m-d H:i:s');
        $config->save();

        $this->sendStats();
    }

    /**
     * Send installation stats
     *
     * @return void
     */
    protected function sendStats()
    {
        $headers = [
            'Authorization: ' . base64_encode('phire-stats-' . time()),
            'User-Agent: ' . (isset($_SERVER['HTTP_USER_AGENT']) ?
                $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:41.0) Gecko/20100101 Firefox/41.0')
        ];

        $curl = new Curl('http://stats.phirecms.org/system', [
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $curl->setPost(true);
        $curl->setFields([
            'version'   => \Phire\Module::VERSION,
            'domain'    => (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
            'ip'        => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
            'os'        => PHP_OS,
            'server'    => (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : ''),
            'php'       => PHP_VERSION,
            'db'        => DB_INTERFACE . ((DB_INTERFACE == 'pdo') ? ' (' . DB_TYPE . ')' : '')
        ]);

        $curl->send();
    }

}