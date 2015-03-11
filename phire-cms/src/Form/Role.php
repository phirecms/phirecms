<?php

namespace Phire\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Validator;

class Role extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Role
     */
    public function __construct(array $fields = null, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'role-form');
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

        if (($_POST) && (null !== $this->name)) {
            $role = Table\Roles::findBy(['name' => $this->name]);
            if (isset($role->id) && ($this->id != $role->id)) {
                $this->getElement('name')
                     ->addValidator(new Validator\NotEqual($this->name, 'That role already exists.'));
            }
        }

        return $this;
    }

}