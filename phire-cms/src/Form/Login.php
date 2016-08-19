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
namespace Phire\Form;

use Phire\Table;
use Pop\Auth\Auth;
use Pop\Form\Form;
use Pop\Validator;

/**
 * Login form class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0
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
     * @return Login
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'login-form');
        $this->setAttribute('class', 'form-signin');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array  $values
     * @param  Auth   $auth
     * @return Login
     */
    public function setFieldValues(array $values = null, Auth $auth = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && (null !== $this->username) && (null !== $this->password) && (null !== $auth)) {
            $auth->authenticate(
                html_entity_decode($this->username, ENT_QUOTES, 'UTF-8'),
                html_entity_decode($this->password, ENT_QUOTES, 'UTF-8')
            );

            if (!($auth->isValid())) {
                $this->getElement('password')
                     ->addValidator(new Validator\NotEqual($this->password, 'The login was not correct.'));
            } else if (!$auth->adapter()->getUser()->verified) {
                $this->getElement('password')
                     ->addValidator(new Validator\NotEqual($this->password, 'That user is not verified.'));
            } else if (!$auth->adapter()->getUser()->active) {
                $this->getElement('password')
                     ->addValidator(new Validator\NotEqual($this->password, 'That user is blocked.'));
            }
        }

        return $this;
    }

}