<?php

namespace Phire\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;

class User extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return User
     */
    public function __construct(array $fields = null, $action = null, $method = 'post')
    {
        $roles = Table\Roles::findAll();
        $roleValues = ['----' => '[Blocked]'];
        foreach ($roles->rows() as $role) {
            $roleValues[$role['id']] = $role['name'];
        }

        $fields = [
            'role_id' => [
                'type'       => 'select',
                'label'      => 'Role',
                'value'      => $roleValues,
                'attributes' => [
                    'onchange' => 'phire.changeRole(this.value, \'' . BASE_PATH . APP_URI . '\');'
                ]
            ],
            'username' => [
                'type'     => 'text',
                'label'    => 'Username',
                'required' => true
            ],
            'email1' => [
                'type'       => 'email',
                'label'      => 'Email',
                'required'   => true,
                'validators' => new Validator\Email()
            ],
            'email2' => [
                'type'      => 'email',
                'label'     => 'Re-Type Email'
            ],
            'password1' => [
                'type'       => 'password',
                'label'      => 'Password',
                'validators' => new Validator\LengthGte(6)
            ],
            'password2' => [
                'type'      => 'password',
                'label'     => 'Re-Type Password'
            ],
            'verified' => [
                'type'      => 'radio',
                'label'     => 'Verified',
                'value' => [
                    '1' => 'Yes',
                    '0' => 'No'
                ],
                'marked' => 0
            ],
            'submit' => [
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Save'
            ],
            'id' => [
                'type'  => 'hidden',
                'value' => '0'
            ]
        ];


        parent::__construct($fields, $action, $method);

        $this->setAttribute('id', 'user-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  array $filters
     * @return Login
     */
    public function setFieldValues(array $values = null, array $filters = null)
    {

        parent::setFieldValues($values, $filters);

        // Change username to hidden if email used instead
        if (null !== $this->role_id) {
            $index = $this->getElementIndex('username');
            $role  = Table\Roles::findById((int)$this->role_id);
            if (isset($role->id)) {
                if (($role->email_as_username) && ($this->childNodes[$index] instanceof Element\Input\Text)) {
                    $hidden = new Element\Input\Hidden('username', $this->childNodes[$index]->getValue());
                    $hidden->setLabel('&nbsp;');
                    $this->childNodes[$index] = $hidden;
                    $this->getElement('email1')->setAttribute('onkeydown', 'phire.changeUsername();');
                }
            }
        }

        if (($_POST) && (null !== $this->username)) {
            $user = Table\Users::findBy(['username' => $this->username]);
            if (isset($user->id) && ($this->id != $user->id)) {
                $this->getElement('username')
                     ->addValidator(new Validator\NotEqual($this->username, 'That username already exists.'));
            }

            $email = Table\Users::findBy(['email' => $this->email1]);
            if (isset($email->id) && ($this->id != $email->id)) {
                $this->getElement('email1')
                     ->addValidator(new Validator\NotEqual($this->email1, 'That email already exists.'));
            }

            // If existing
            if ((int)$_POST['id'] > 0) {
                if (($user->email !== $this->email1) && ($email->email !== $this->email1)) {
                    $this->getElement('email2')
                         ->setRequired(true)
                         ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
                }
                if (!empty($this->password1)) {
                    $this->getElement('password2')
                         ->setRequired(true)
                         ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
                }
            // Else, if new
            } else {
                $this->getElement('email2')
                     ->setRequired(true)
                     ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
                $this->getElement('password2')
                     ->setRequired(true)
                     ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
            }
        }
    }

}