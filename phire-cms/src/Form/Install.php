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
namespace Phire\Form;

use Pop\Db\Db;
use Pop\Form\Form;
use Pop\Validator;

/**
 * Install form class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
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
     */
    public function __construct(array $fields = null, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'install-form');
        $this->setAttribute('class', 'form-signin');
        $this->setIndent('        ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return Install
     */
    public function setFieldValues(array $values)
    {
        parent::setFieldValues($values);

        if (($_POST) && !empty($this->db_adapter)) {
            // If not SQLite, check the DB parameters
            if (stripos($this->db_adapter, 'sqlite') === false) {
                $this->getField('db_name')->addValidator(
                    new Validator\NotEmpty(null, 'The database name is required.')
                );
                $this->getField('db_username')->addValidator(
                    new Validator\NotEmpty(null, 'The database username is required.')
                );
                $this->getField('db_password')->addValidator(
                    new Validator\NotEmpty(null, 'The database password is required.')
                );
                $this->getField('db_host')->addValidator(
                    new Validator\NotEmpty(null, 'The database host is required.')
                );
            }

            // Check the content path
            if (!$this->checkContentPath()) {
                $this->getField('content_path')->addValidator(
                    new Validator\NotEqual($this->content_path,
                        wordwrap(
                            'The content directory (or subdirectories) either do not exist or are not writable.',
                            40, '<br />'
                        )
                    )
                );
            }

            // Check the database credentials
            if (stripos($this->db_adapter, 'sqlite') === false) {
                if (stripos($this->db_adapter, 'pdo_') !== false) {
                    $adapter = 'Pdo';
                    $type    = str_replace('pdo_', '', strtolower($this->db_adapter));
                } else {
                    $adapter = ucfirst(strtolower($this->db_adapter));
                    $type    = null;
                }

                $oldError = ini_get('error_reporting');
                error_reporting(E_ERROR);

                $options = [
                    'database' => $this->db_name,
                    'username' => $this->db_username,
                    'password' => $this->db_password,
                    'host'     => $this->db_host,
                    'type'     => $type,
                ];

                $dbCheck = Db::check($adapter, $options);

                // If there is a DB error
                if (null != $dbCheck) {
                    $this->getField('db_adapter')->addValidator(
                        new Validator\NotEqual($this->db_adapter, wordwrap($dbCheck, 40, '<br />'))
                    );
                } else {
                    $db = Db::connect($adapter, $options);

                    $version = $db->getVersion();
                    $version = substr($version, (strrpos($version, ' ') + 1));
                    if (strpos($version, '-') !== false) {
                        $version = substr($version, 0, strpos($version, '-'));
                    }

                    if ((stripos($this->db_adapter, 'mysql') !== false) && (version_compare($version, '5.0') < 0)) {
                        $this->getField('db_adapter')->addValidator(
                            new Validator\NotEqual($this->db_adapter, 'The MySQL version must be 5.0 or greater.')
                        );
                    } else if ((stripos($this->db_adapter, 'pgsql') !== false) && (version_compare($version, '9.0') < 0)) {
                        $this->getField('db_adapter')->addValidator(
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