<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Db\Db;
use Pop\Form\Form;
use Pop\I18n\I18n;
use Pop\Project\Install\Dbs;
use Pop\Validator;
use Pop\Version;

class Install extends Form
{

    /**
     * Available database adapters
     * @var array
     */
    protected $dbAdapters = array();

    /**
     * DB versions
     * @var array
     */
    protected static $dbVersions = array(
        'Mysql'  => '5.0',
        'Pgsql'  => '9.0'
    );

    /**
     * Language object
     * @var \Pop\I18n\I18n
     */
    protected $i18n = null;

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @return self
     */
    public function __construct($action = null, $method = 'post')
    {
        $lang = (isset($_GET['lang'])) ? $_GET['lang'] : 'en_US';
        if (!defined('POP_LANG')) {
            define('POP_LANG', $lang);
        }

        $this->i18n = I18n::factory();
        $this->i18n->loadFile(__DIR__ . '/../../../data/assets/i18n/' . $this->i18n->getLanguage() . '.xml');

        $this->initFieldsValues = $this->getInitFields();
        parent::__construct($action, $method, null, '        ');
        $this->setAttributes('id', 'install-form');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  array $filters
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null)
    {
        parent::setFieldValues($values, $filters);

        if ($_POST) {
            // Check the content directory
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $this->content_path)) {
                $this->getElement('content_path')->addValidator(new Validator\NotEqual($this->content_path, $this->i18n->__('The content directory does not exist.')));
            } else {
                $checkDirs = \Phire\Project::checkDirs($_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $this->content_path, true);
                if (count($checkDirs) > 0) {
                    $this->getElement('content_path')->addValidator(new Validator\NotEqual($this->content_path, $this->i18n->__('The content directory (or subdirectories) are not writable.')));
                }
            }

            // If not SQLite, check the DB parameters
            if (strpos($this->db_adapter, 'Sqlite') === false) {
                $this->getElement('db_name')->addValidator(new Validator\NotEmpty(null, $this->i18n->__('The database name is required.')));
                $this->getElement('db_username')->addValidator(new Validator\NotEmpty(null, $this->i18n->__('The database username is required.')));
                $this->getElement('db_password')->addValidator(new Validator\NotEmpty(null, $this->i18n->__('The database password is required.')));
                $this->getElement('db_host')->addValidator(new Validator\NotEmpty(null, $this->i18n->__('The database host is required.')));
            }

            // Check the database credentials
            if ($this->isValid()) {
                $oldError = ini_get('error_reporting');
                error_reporting(E_ERROR);

                $dbCheck = Dbs::check(array(
                    'database' => $this->db_name,
                    'username' => $this->db_username,
                    'password' => $this->db_password,
                    'host'     => $this->db_host,
                    'type'     => str_replace('\\', '_', $this->db_adapter),
                ));

                // If there is a DB error
                if (null != $dbCheck) {
                    $this->getElement('db_adapter')->addValidator(new Validator\NotEqual($this->db_adapter, wordwrap($dbCheck, 50, '<br />')));
                } else {
                    // Check the database version
                    if (strpos($this->db_adapter, 'Sqlite') === false) {
                        $adapter = (stripos($this->db_adapter, 'Pdo\\') !== false) ? str_replace('Pdo\\', '', $this->db_adapter) : $this->db_adapter;
                        $db = Db::factory($adapter, array(
                            'database' => $this->db_name,
                            'username' => $this->db_username,
                            'password' => $this->db_password,
                            'host'     => $this->db_host,
                            'type'     => strtolower(str_replace('Pdo\\', '', $this->db_adapter))
                        ));

                        $version = $db->adapter()->version();
                        $version = substr($version, (strrpos($version, ' ') + 1));
                        if (strpos($version, '-') !== false) {
                            $version = substr($version, 0, strpos($version, '-'));
                        }

                        if (stripos($this->db_adapter, 'Mysql') !== false) {
                            $dbVerKey = 'Mysql';
                        } else if (stripos($this->db_adapter, 'Pgsql') !== false) {
                            $dbVerKey = 'Pgsql';
                        } else {
                            $dbVerKey = null;
                        }

                        if ((null !== $dbVerKey) && (version_compare($version, self::$dbVersions[$dbVerKey]) < 0)) {
                            $this->getElement('db_adapter')->addValidator(new Validator\NotEqual($this->db_adapter, wordwrap($this->i18n->__('The %1 database version must be %2 or greater. (%3 detected.)', array($dbVerKey, self::$dbVersions[$dbVerKey], $version)), 45, '<br />')));
                        }
                    }
                }

                error_reporting($oldError);
            }
        }

        return $this;
    }

    /**
     * Get the init field values
     *
     * @return array
     */
    protected function getInitFields()
    {
        $check = Version::check(Version::DATA);

        foreach ($check as $key => $value) {
            if (strpos($key, 'db') !== false) {
                if (($value == 'Yes') && (stripos($key, 'sqlsrv') === false) && (stripos($key, 'oracle') === false)) {
                    $db = str_replace('db', '', $key);
                    if ((strpos($db, 'Pdo') !== false) && ($db != 'Pdo')) {
                        $db = 'Pdo\\' . ucfirst(strtolower(str_replace('Pdo', '', $db)));
                        $this->dbAdapters[$db] = $db;
                    } else if ($db != 'Pdo') {
                        $db = ucfirst(strtolower($db));
                        if ($db != 'Mysql') {
                            $this->dbAdapters[$db] = $db;
                        }
                    }
                }
            }
        }

        $langs = I18n::getLanguages(__DIR__ . '/../../../data/assets/i18n');
        foreach ($langs as $key => $value) {
            $langs[$key] = substr($value, 0, strpos($value, ' ('));
        }

        $fields = array(
            'language' => array (
                'type' => 'select',
                'label' => $this->i18n->__('Language'),
                'value' => $langs,
                'marked' => POP_LANG,
                'attributes' => array(
                    'onchange' => "changeLanguage(this);",
                    'style'    => 'width: 260px;'
                )
            ),
            'db_adapter' => array (
                'type' => 'select',
                'label' => $this->i18n->__('DB Adapter'),
                'required' => true,
                'value' => $this->dbAdapters,
                'attributes' => array('style' => 'width: 260px;')
            ),
            'db_name' => array (
                'type' => 'text',
                'label' => $this->i18n->__('DB Name'),
                'attributes' => array('size' => 30)
            ),
            'db_username' => array (
                'type' => 'text',
                'label' => $this->i18n->__('DB Username'),
                'attributes' => array('size' => 30)
            ),
            'db_password' => array (
                'type' => 'text',
                'label' => $this->i18n->__('DB Password'),
                'attributes' => array('size' => 30)
            ),
            'db_host' => array (
                'type' => 'text',
                'label' => $this->i18n->__('DB Host'),
                'attributes' => array('size' => 30),
                'value' => 'localhost'
            ),
            'db_prefix' => array (
                'type' => 'text',
                'name' => 'db_prefix',
                'label' => $this->i18n->__('DB Table Prefix'),
                'attributes' => array('size' => 30),
                'value' => 'ph_'
            ),
            'app_uri' => array (
                'type' => 'text',
                'label' => $this->i18n->__('Application URI') . '<br /><em style="font-size: 0.9em; color: #666; font-weight: normal;">(' . $this->i18n->__('How you will access the system') . ')</em>',
                'attributes' => array('size' => 30),
                'value' => APP_URI
            ),
            'content_path' => array (
                'type' => 'text',
                'label' => $this->i18n->__('Content Path') . '<br /><em style="font-size: 0.9em; color: #666; font-weight: normal;">(' . $this->i18n->__('Where assets will be located') . ')</em>',
                'required' => true,
                'attributes' => array('size' => 30),
                'value' => CONTENT_PATH
            ),
            'password_encryption' => array (
                'type' => 'hidden',
                'value' => 4
            ),
            'submit' => array (
                'type' => 'submit',
                'label' => '&nbsp;',
                'value' => $this->i18n->__('NEXT'),
                'attributes' => array(
                    'class' => 'install-btn'
                )
            )
        );

        return $fields;
    }

}

