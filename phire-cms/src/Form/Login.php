<?php

namespace Phire\Form;

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

}