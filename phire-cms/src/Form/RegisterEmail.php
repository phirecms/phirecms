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
use Pop\Form\Form;
use Pop\Validator;

/**
 * RegisterEmail Form class
 *
 * @category   Phire
 * @package    Phire
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    2.0.1
 */
class RegisterEmail extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  boolean $captcha
     * @param  boolean $csrf
     * @param  array   $fields
     * @param  string  $action
     * @param  string  $method
     * @return RegisterEmail
     */
    public function __construct($captcha = false, $csrf = false, array $fields = null, $action = null, $method = 'post')
    {
        if ($csrf) {
            $fields[2] = array_merge([
                'csrf'   => [
                    'type'  => 'csrf'
                ]
            ], $fields[2]);
        }

        if ($captcha) {
            $fields[2] = array_merge([
                'captcha'   => [
                    'type'  => 'captcha',
                    'label' => 'Please Solve:'
                ]
            ], $fields[2]);
        }

        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'register-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return RegisterEmail
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && (null !== $this->email)) {
            // Check for dupe email
            if (null !== $this->email) {
                $user = Table\Users::findBy(['username' => $this->email]);
                if (isset($user->id)) {
                    $this->getElement('email')
                         ->addValidator(new Validator\NotEqual($this->email, 'That username is not allowed.'));
                } else {
                    $email = Table\Users::findBy(['email' => $this->email]);
                    if (isset($email->id)) {
                        $this->getElement('email')
                             ->addValidator(new Validator\NotEqual($this->email, 'That email is not allowed.'));
                    }
                }
            }

            // Check password matches
            $this->getElement('password2')
                 ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
        }

        return $this;
    }

}