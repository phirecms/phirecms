<?php

namespace Phire\Form;

use Pop\Auth\Auth;
use Pop\Form\Form;
use Pop\Validator;

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
    public function __construct(array $fields = null, $action = null, $method = 'post')
    {
        $fields = [
            'username' => [
                'type'      => 'text',
                'label'     => 'Username',
                'required'  => 'true',
                'validator' => new Validator\NotEmpty()
            ],
            'password' => [
                'type'      => 'password',
                'label'     => 'Password',
                'required'  => 'true',
                'validator' => new Validator\NotEmpty()
            ],
            'submit' => [
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Login'
            ]
        ];
        parent::__construct($fields, $action, $method);
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  array $filters
     * @param  Auth  $auth
     * @return Login
     */
    public function setFieldValues(array $values = null, array $filters = null, Auth $auth = null)
    {
        parent::setFieldValues($values, $filters);

        if (($_POST) && (null !== $this->username) && (null !== $this->password) && (null !== $auth)) {

            $auth->authenticate($this->username, $this->password);

            if (!($auth->isValid())) {
                $this->getElement('password')
                     ->addValidator(new Validator\NotEqual($this->password, 'The username or password were not correct.'));
            }
        }

        return $this;
    }

}