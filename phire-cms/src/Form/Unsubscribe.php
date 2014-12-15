<?php

namespace Phire\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Validator;

class Unsubscribe extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Unsubscribe
     */
    public function __construct(array $fields = null, $action = null, $method = 'post')
    {
        $fields = [
            'email' => [
                'type'       => 'email',
                'label'      => 'Email',
                'required'   => 'true',
                'validators' => new Validator\Email()
            ],
            'submit' => [
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => 'Unsubscribe'
            ]
        ];

        parent::__construct($fields, $action, $method);

        $this->setAttribute('id', 'unsubscribe-form');
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

        if (($_POST) && (null !== $this->email)) {
            $user = Table\Users::findBy(['email' => $this->email]);
            if (!isset($user->id)) {
                $this->getElement('email')
                     ->addValidator(new Validator\NotEqual($this->email, 'That email does not exist.'));
            } else if (null !== $user->role_id) {
                $role = Table\Roles::findById($user->role_id);
                if (null !== $role->permissions) {
                    $permissions = unserialize($role->permissions);
                    if (!isset($permissions[APP_URI . '/login[/]']) ||
                        ((isset($permissions[APP_URI . '/login[/]']) && ($permissions[APP_URI . '/login[/]'])))) {
                            $this->getElement('email')
                                 ->addValidator(new Validator\NotEqual($this->email, 'You must <a href="' .
                                     BASE_PATH . APP_URI . '/login">log in</a> to unsubscribe.'));
                    }
                }
            }
        }

        return $this;
    }

}