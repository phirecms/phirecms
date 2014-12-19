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
     * @param  int    $id
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Register
     */
    public function __construct($id, array $fields = null, $action = null, $method = 'post')
    {
        $role = Table\UserRoles::findById($id);

        $fields = [
            'username' => [
                'type'     => ($role->email_as_username) ? 'hidden' : 'text',
                'label'    => ($role->email_as_username) ? '&nbsp;' : 'Username',
                'required' => !($role->email_as_username)
            ],
            'email1' => [
                'type'       => 'email',
                'label'      => 'Email',
                'required'   => true,
                'validators' => new Validator\Email()
            ],
            'email2' => [
                'type'      => 'email',
                'required'  => true,
                'label'     => 'Re-Type Email'
            ],
            'password1' => [
                'type'       => 'password',
                'label'      => 'Password',
                'required'   => true,
                'validators' => new Validator\LengthGte(6)
            ],
            'password2' => [
                'type'      => 'password',
                'required'  => true,
                'label'     => 'Re-Type Password'
            ],
            'submit' => [
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Register'
            ],
            'role_id' => [
                'type'  => 'hidden',
                'value' => $id
            ]
        ];

        if ($role->email_as_username) {
            $fields['email1']['attributes'] = [
                'onblur' => 'phire.changeUsername()'
            ];
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

        if (($_POST) && (null !== $this->username)) {
            $user = Table\Users::findBy(['username' => $this->username]);
            if (isset($user->id)) {
                $this->getElement('username')
                     ->addValidator(new Validator\NotEqual($this->username, 'That username already exists.'));
            }

            $email = Table\Users::findBy(['email' => $this->email1]);
            if (isset($email->id)) {
                $this->getElement('email1')
                     ->addValidator(new Validator\NotEqual($this->email1, 'That email already exists.'));
            }

            $this->getElement('email2')
                 ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
            $this->getElement('password2')
                 ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
        }
    }

}