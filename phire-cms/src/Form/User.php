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
     * @param  \Pop\Acl\Acl $acl
     * @param  \ArrayObject $user
     * @param  array        $fields
     * @param  string       $action
     * @param  string       $method
     * @return User
     */
    public function __construct($acl, $user, array $fields = null, $action = null, $method = 'post')
    {
        $roles      = Table\UserRoles::findAll();
        $roleValues = ['----' => '[Blocked]'];
        foreach ($roles->rows() as $role) {
            if (($acl->hasResource('user-role-' . $role['id'])) &&
                ($acl->isAllowed($user->role, 'user-role-' . $role['id'], 'add'))) {
                $roleValues[$role['id']] = $role['name'];
            }
        }

        $fields = [
            [
                'submit' => [
                    'type'       => 'submit',
                    'value'      => 'Save',
                    'attributes' => [
                        'class'  => 'save-btn wide'
                    ]
                ],
                'role_id' => [
                    'type'       => 'select',
                    'label'      => 'Role',
                    'value'      => $roleValues,
                    'attributes' => [
                        'class'    => 'wide',
                        'onchange' => 'phire.changeRole(this.value, \'' . BASE_PATH . APP_URI . '\');'
                    ]
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
                'id' => [
                    'type'  => 'hidden',
                    'value' => '0'
                ]
            ],
            [
                'username' => [
                    'type'     => 'text',
                    'label'    => 'Username',
                    'required' => true,
                    'attributes' => [
                        'size'    => 40
                    ]
                ],
                'email1' => [
                    'type'       => 'email',
                    'label'      => 'Email',
                    'required'   => true,
                    'validators' => new Validator\Email(),
                    'attributes' => [
                        'size'    => 40
                    ]
                ],
                'email2' => [
                    'type'      => 'email',
                    'label'     => 'Re-Type Email',
                    'attributes' => [
                        'size'    => 40
                    ]
                ],
                'password1' => [
                    'type'       => 'password',
                    'label'      => 'Password',
                    'validators' => new Validator\LengthGte(6),
                    'attributes' => [
                        'size'    => 40
                    ]
                ],
                'password2' => [
                    'type'      => 'password',
                    'label'     => 'Re-Type Password',
                    'attributes' => [
                        'size'    => 40
                    ]
                ]
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
     * @return User
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        // Change username to hidden if email used instead
        if (null !== $this->role_id) {
            $index = $this->getElementIndex('username');
            $role  = Table\UserRoles::findById((int)$this->role_id);
            if (isset($role->id)) {
                if (($role->email_as_username) && ($this->childNodes[$index] instanceof Element\Input\Text)) {
                    $hidden = new Element\Input\Hidden('username', $this->childNodes[$index]->getValue());
                    $hidden->setLabel('Username');
                    $hidden->setRequired(true);
                    $this->childNodes[$index] = $hidden;
                    $this->getElement('email1')->setAttribute('onblur', 'phire.changeUsername();')
                                               ->setAttribute('onkeyup', 'phire.changeTitle(this.value);');
                } else {
                    $this->getElement('username')->setAttribute('onkeyup', 'phire.changeTitle(this.value);');
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

            // If existing user
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
            // Else, if new user
            } else {
                $this->getElement('email2')
                     ->setRequired(true)
                     ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
                $this->getElement('password2')
                     ->setRequired(true)
                     ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
            }
        }

        return $this;
    }

}