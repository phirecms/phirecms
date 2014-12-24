<?php

namespace Phire\Form;

use Phire\Table;
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
            [
                'username' => [
                    'type'       => 'text',
                    'label'      => 'Username',
                    'required'   => 'true',
                    'validators' => new Validator\NotEmpty()
                ],
                'password' => [
                    'type'       => 'password',
                    'label'      => 'Password',
                    'required'   => 'true',
                    'validators' => new Validator\NotEmpty()
                ]
            ],
            [
                'submit' => [
                    'type'  => 'submit',
                    'value' => 'Login',
                    'attributes' => [
                        'class'  => 'save-btn'
                    ]
                ]
            ]
        ];

        parent::__construct($fields, $action, $method);

        $this->setAttribute('id', 'login-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  Auth  $auth
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
                     ->addValidator(new Validator\NotEqual($this->password, 'The username or password were not correct.'));
            } else if (!$auth->adapter()->getUser()->verified) {
                $this->getElement('password')
                     ->addValidator(new Validator\NotEqual($this->password, 'That user is not verified.'));
            } else if (null === $auth->adapter()->getUser()->role_id) {
                $this->getElement('password')
                     ->addValidator(new Validator\NotEqual($this->password, 'That user is blocked.'));
            } else  {
                $role = Table\UserRoles::findById($auth->adapter()->getUser()->role_id);
                if (isset($role->id) && (null !== $role->permissions)) {
                    $permissions = unserialize($role->permissions);
                    if (isset($permissions['deny'])) {
                        foreach ($permissions['deny'] as $deny) {
                            if ($deny['resource'] == 'login') {
                                $this->getElement('password')
                                     ->addValidator(new Validator\NotEqual(
                                         $this->password, 'That user role is not allowed to login.'
                                     ));
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

}