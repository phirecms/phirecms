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

use Pop\Auth\Table as Auth;
use Pop\Form\Form;
use Pop\Validator;

/**
 * Login form class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Login extends Form
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
        $this->setAttribute('id', 'login-form');
        $this->setAttribute('class', 'form-signin');
        $this->setIndent('        ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  Auth  $auth
     * @param  int   $attempts
     * @return Login
     */
    public function setFieldValues(array $values, Auth $auth = null, $attempts = 0)
    {
        parent::setFieldValues($values);

        if (($_POST) && (null !== $this->username) && (null !== $this->password) && (null !== $auth)) {
            $auth->authenticate(
                html_entity_decode($this->username, ENT_QUOTES, 'UTF-8'),
                html_entity_decode($this->password, ENT_QUOTES, 'UTF-8')
            );

            if (!($auth->isValid())) {
                $this->getField('password')
                     ->addValidator(new Validator\NotEqual($this->password, 'The login was not correct.'));
            } else if (!$auth->getUser()->verified) {
                $this->getField('password')
                     ->addValidator(new Validator\NotEqual($this->password, 'That user is not verified.'));
            } else if (!$auth->getUser()->active) {
                $this->getField('password')
                     ->addValidator(new Validator\NotEqual($this->password, 'That user is blocked.'));
            } else if (((int)$attempts > 0) && ((int)$auth->getUser()->failed_attempts >= (int)$attempts)) {
                $this->getField('password')
                    ->addValidator(new Validator\NotEqual($this->password, 'Login failed.'));
            }
        }

        return $this;
    }

}