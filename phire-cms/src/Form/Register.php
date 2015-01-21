<?php

namespace Phire\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Validator;

class Register extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  int     $rid
     * @param  boolean $captcha
     * @param  boolean $csrf
     * @param  array   $fields
     * @param  string  $action
     * @param  string  $method
     * @return Register
     */
    public function __construct($rid, $captcha = false, $csrf = false, array $fields = null, $action = null, $method = 'post')
    {
        $role = Table\UserRoles::findById($rid);

        if ($role->email_as_username) {
            unset($fields[0]['username']);
        }

        $fields[1]['role_id']['value'] = $rid;

        if ($csrf) {
            $fields[1] = array_merge([
                'csrf'   => [
                    'type'  => 'csrf'
                ]
            ], $fields[1]);
        }

        if ($captcha) {
            $fields[1] = array_merge([
                'captcha'   => [
                    'type'  => 'captcha',
                    'label' => 'Please Solve:'
                ]
            ], $fields[1]);
        }

        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'register-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return Register
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && (null !== $this->email1)) {
            // Check for dupe username
            if (null !== $this->username) {
                $user = Table\Users::findBy(['username' => $this->username]);
                if (isset($user->id)) {
                    $this->getElement('username')
                         ->addValidator(new Validator\NotEqual($this->username, 'That username is not allowed.'));
                }
            }

            // Check for dupe email
            $email = Table\Users::findBy(['email' => $this->email1]);
            if (isset($email->id)) {
                $this->getElement('email1')
                     ->addValidator(new Validator\NotEqual($this->email1, 'That email is not allowed.'));
            }

            // Check email and password matches
            $this->getElement('email2')
                 ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
            $this->getElement('password2')
                 ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
        }

        return $this;
    }

}