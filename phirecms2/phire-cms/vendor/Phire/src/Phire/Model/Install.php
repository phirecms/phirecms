<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;
use Pop\Db\Db;
use Pop\File\File;
use Pop\Filter\String;
use Pop\Mail\Mail;
use Pop\Project\Install\Dbs;
use Pop\Web\Server;
use Pop\Web\Session;

class Install
{

    /**
     * Model data
     *
     * @var array
     */
    protected $data = array();

    /**
     * Instantiate the model object.
     *
     * @param  array $data
     * @return self
     */
    public function __construct(array $data = null)
    {
        if (null !== $data) {
            $this->data = $data;
        }
    }

    /**
     * Get model data
     *
     * @param  string $key
     * @return mixed
     */
    public function getData($key = null)
    {
        if (null !== $key) {
            return (isset($this->data[$key])) ? $this->data[$key] : null;
        } else {
            return $this->data;
        }
    }

    /**
     * Set model data
     *
     * @param  string $name,
     * @param  mixed $value
     * @return self
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
        return $this;
    }

    /**
     * Install config method
     *
     * @param mixed  $form
     * @param string $docRoot
     * @return void
     */
    public function config($form, $docRoot = null)
    {
        if (null === $docRoot) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'] . BASE_PATH;
        }

        // Get config file contents
        $cfgFile = new File($docRoot . '/config.php');
        $config = $cfgFile->read();

        // Get DB interface and type
        if (strpos($form->db_adapter, 'Pdo') !== false) {
            $dbInterface = 'Pdo';
            $dbType = strtolower(substr($form->db_adapter, (strrpos($form->db_adapter, '\\') + 1)));
        } else {
            $dbInterface = html_entity_decode($form->db_adapter, ENT_QUOTES, 'UTF-8');
            $dbType = null;
        }

        // If DB is SQLite
        if (strpos($form->db_adapter, 'Sqlite') !== false) {
            touch($docRoot . $form->content_path . '/.htphire.sqlite');
            $relativeDbName = "__DIR__ . '" . $form->content_path . '/.htphire.sqlite';
            $dbName = realpath($docRoot . $form->content_path . '/.htphire.sqlite');
            $dbUser = null;
            $dbPassword = null;
            $dbHost = null;
            $installFile = $dbName;
            chmod($dbName, 0777);
        } else {
            $relativeDbName = null;
            $dbName = $form->db_name;
            $dbUser = $form->db_username;
            $dbPassword = $form->db_password;
            $dbHost = $form->db_host;
            $installFile = null;
        }

        $dbPrefix = $form->db_prefix;

        // Set config values
        $config = str_replace("define('CONTENT_PATH', '/phire-content');", "define('CONTENT_PATH', '" . $form->content_path . "');", $config);
        $config = str_replace("define('APP_URI', '/phire');", "define('APP_URI', '" . $form->app_uri . "');", $config);
        $config = str_replace("define('DB_INTERFACE', '');", "define('DB_INTERFACE', '" . $dbInterface . "');", $config);
        $config = str_replace("define('DB_TYPE', '');", "define('DB_TYPE', '" . $dbType . "');", $config);
        $config = str_replace("define('DB_NAME', '');", "define('DB_NAME', " . ((null !== $relativeDbName) ? $relativeDbName : "'" . $dbName) . "');", $config);
        $config = str_replace("define('DB_USER', '');", "define('DB_USER', '" . $dbUser . "');", $config);
        $config = str_replace("define('DB_PASS', '');", "define('DB_PASS', '" . $dbPassword . "');", $config);
        $config = str_replace("define('DB_HOST', '');", "define('DB_HOST', '" . $dbHost . "');", $config);
        $config = str_replace("define('DB_PREFIX', '');", "define('DB_PREFIX', '" . $dbPrefix . "');", $config);

        $this->data['configWritable'] = is_writable($docRoot . '/config.php');

        if ($form instanceof \Pop\Form\Form) {
            // Store the config values in session in case config file is not writable.
            $sess = Session::getInstance();
            $sess->config = serialize(htmlentities($config, ENT_QUOTES, 'UTF-8'));
            $sess->app_uri = $form->app_uri;
        }

        if ($this->data['configWritable']) {
            $cfgFile->write($config)->save();
        }

        // Install the database
        $sqlFile = __DIR__ . '/../../../data/phire.' . str_replace(array('pdo\\', 'mysqli' ), array('', 'mysql'), strtolower($form->db_adapter)) . '.sql';

        $db = array(
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPassword,
            'host'     => $dbHost,
            'prefix'   => $dbPrefix,
            'type'     => str_replace('\\', '_', $form->db_adapter)
        );

        Dbs::install($dbName, $db, $sqlFile, $installFile, true);

        if (stripos($form->db_adapter, 'Pdo\\') !== false) {
            $adapter = 'Pdo';
            $type = strtolower(substr($form->db_adapter, (strpos($form->db_adapter, '\\') + 1)));
        } else {
            $adapter = $form->db_adapter;
            $type = null;
        }

        // Set the default system config
        $db = Db::factory($adapter, array(
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPassword,
            'host'     => $dbHost,
            'type'     => $type
        ));

        // Get server info
        if (isset($_SERVER) && isset($_SERVER['SERVER_SOFTWARE'])) {
            $server = new Server();
            $os     = $server->getOs() . ' (' . $server->getDistro() . ')';
            $srv    = $server->getServer() . ' ' . $server->getServerVersion();
            $domain = $_SERVER['HTTP_HOST'];
            $doc    = $_SERVER['DOCUMENT_ROOT'];
        } else {
            $os     = '';
            $srv    = '';
            $domain = '';
            $doc    = '';
        }

        // Set the system configuration
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . \Phire\Project::VERSION . "' WHERE setting = 'system_version'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . $db->adapter()->escape($domain) . "' WHERE setting = 'system_domain'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . $db->adapter()->escape($doc) . "' WHERE setting = 'system_document_root'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . $db->adapter()->escape($os) . "' WHERE setting = 'server_operating_system'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . $db->adapter()->escape($srv) . "' WHERE setting = 'server_software'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . $db->adapter()->version() . "' WHERE setting = 'database_version'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . PHP_VERSION . "' WHERE setting = 'php_version'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . date('Y-m-d H:i:s') . "' WHERE setting = 'installed_on'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "config SET value = '" . $db->adapter()->escape($form->language) . "' WHERE setting = 'default_language'");
        $db->adapter()->query("UPDATE " . $db->adapter()->escape($dbPrefix) . "user_types SET password_encryption = '" . $db->adapter()->escape((int)$form->password_encryption) . "' WHERE id = 2001");
    }

    /**
     * Send install notification email to user
     *
     * @param  \Phire\Form\User $form
     * @return void
     */
    public static function send(\Phire\Form\User $form)
    {
        $i18n = Table\Config::getI18n();

        // Get the domain
        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']);

        // Set the recipient
        $rcpt = array(
            'name'   => $form->username,
            'email'  => $form->email1,
            'url'    => 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH,
            'login'  => 'http://' . $_SERVER['HTTP_HOST'] . BASE_PATH . APP_URI,
            'domain' => $domain
        );

        $config = \Phire\Table\Config::findById('system_email');
        $config->value = $form->email1;
        $config->update();

        $config = \Phire\Table\Config::findById('reply_email');
        $config->value = 'noreply@' . $domain;
        $config->update();

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/mail')) {
            $mailTmpl = file_get_contents($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . CONTENT_PATH . '/extensions/themes/phire/mail/install.txt');
        } else {
            $mailTmpl = file_get_contents(__DIR__ . '/../../../view/phire/mail/install.txt');
        }

        $mailTmpl = str_replace(
            array(
                'Dear',
                'Thank you for installing Phire CMS for',
                'The website will be viewable here:',
                'To manage the website, you can login to Phire here:',
                'Thank You'
            ),
            array(
                $i18n->__('Dear'),
                $i18n->__('Thank you for installing Phire CMS for'),
                $i18n->__('The website will be viewable here:'),
                $i18n->__('To manage the website, you can login to Phire here:'),
                $i18n->__('Thank You')
            ),
            $mailTmpl
        );

        // Send email verification
        $mail = new Mail($domain . ' - ' . $i18n->__('Phire CMS Installation'), $rcpt);
        $mail->from('noreply@' . $domain);
        $mail->setText($mailTmpl);
        $mail->send();
    }

}

