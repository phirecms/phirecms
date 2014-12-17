<?php

namespace Phire\Form;

use Pop\Db\Db;
use Pop\Form\Form;
use Pop\Validator;

class Install extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Install
     */
    public function __construct(array $fields = null, $action = null, $method = 'post')
    {
        $fields = [
            'db_adapter'   => [
                'type'     => 'select',
                'label'    => 'DB Adapter',
                'value'    => $this->getDbAdapters()
            ],
            'db_name'   => [
                'type'  => 'text',
                'label' => 'DB Name'
            ],
            'db_username' => [
                'type'    => 'text',
                'label'   => 'DB Username'
            ],
            'db_password' => [
                'type'    => 'text',
                'label'   => 'DB Password'
            ],
            'db_host'   => [
                'type'  => 'text',
                'label' => 'DB Host',
                'value' => 'localhost'
            ],
            'db_prefix' => [
                'type'  => 'text',
                'name'  => 'db_prefix',
                'label' => 'DB Table Prefix',
                'value' => 'ph_'
            ],
            'app_uri'   => [
                'type'  => 'text',
                'label' => 'Application URI',
                'value' => APP_URI
            ],
            'content_path' => [
                'type'     => 'text',
                'label'    => 'Content Path',
                'required' => true,
                'value'    => CONTENT_PATH
            ],
            'submit' => [
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Submit'
            ]
        ];

        parent::__construct($fields, $action, $method);

        $this->setAttribute('id', 'install-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return Install
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && !empty($this->db_adapter)) {
            // If not SQLite, check the DB parameters
            if (stripos($this->db_adapter, 'sqlite') === false) {
                $this->getElement('db_name')->addValidator(
                    new Validator\NotEmpty(null, 'The database name is required.')
                );
                $this->getElement('db_username')->addValidator(
                    new Validator\NotEmpty(null, 'The database username is required.')
                );
                $this->getElement('db_password')->addValidator(
                    new Validator\NotEmpty(null, 'The database password is required.')
                );
                $this->getElement('db_host')->addValidator(
                    new Validator\NotEmpty(null, 'The database host is required.')
                );
            }

            // Check the content path
            if (!$this->checkContentPath()) {
                $this->getElement('content_path')->addValidator(
                    new Validator\NotEqual($this->content_path,
                        wordwrap('The content directory (or subdirectories) either do not exist or are not writable.', 50, '<br />')
                    )
                );
            }

            // Check the database credentials
            if ($this->isValid() && (stripos($this->db_adapter, 'sqlite') === false)) {
                if (stripos($this->db_adapter, 'pdo_') !== false) {
                    $adapter = 'Pdo';
                    $type    = str_replace('pdo_', '', strtolower($this->db_adapter));
                } else {
                    $adapter = ucfirst(strtolower($this->db_adapter));
                    $type    = null;
                }

                $oldError = ini_get('error_reporting');
                error_reporting(E_ERROR);

                $creds = [
                    'database' => $this->db_name,
                    'username' => $this->db_username,
                    'password' => $this->db_password,
                    'host' => $this->db_host,
                    'type' => $type,
                ];

                $dbCheck = Db::check($creds, $adapter);

                // If there is a DB error
                if (null != $dbCheck) {
                    $this->getElement('db_adapter')->addValidator(
                        new Validator\NotEqual($this->db_adapter, wordwrap($dbCheck, 50, '<br />'))
                    );
                } else {
                    $db = Db::connect($adapter, $creds);

                    $version = $db->version();
                    $version = substr($version, (strrpos($version, ' ') + 1));
                    if (strpos($version, '-') !== false) {
                        $version = substr($version, 0, strpos($version, '-'));
                    }

                    if ((stripos($this->db_adapter, 'mysql') !== false) && (version_compare($version, '5.0') < 0)) {
                        $this->getElement('db_adapter')->addValidator(
                            new Validator\NotEqual($this->db_adapter, 'The MySQL version must be 5.0 or greater.')
                        );
                    } else if ((stripos($this->db_adapter, 'pgsql') !== false) && (version_compare($version, '9.0') < 0)) {
                        $this->getElement('db_adapter')->addValidator(
                            new Validator\NotEqual($this->db_adapter, 'The PostgreSQL version must be 9.0 or greater.')
                        );
                    }
                }

                error_reporting($oldError);
            }
        }

        return $this;
    }

    /**
     * Get the DB adapters
     *
     * @return array
     */
    protected function getDbAdapters()
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
     * Check the content path
     *
     * @return boolean
     */
    protected function checkContentPath()
    {
        $check = true;
        $dirs  = [
            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $this->content_path,
            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $this->content_path . '/assets',
            $_SERVER['DOCUMENT_ROOT'] . BASE_PATH . $this->content_path . '/modules',
        ];

        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                $check = false;
            } else if (!is_writable($dir)) {
                $check = false;
            }
        }

        return $check;
    }

}