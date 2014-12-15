<?php

namespace Phire\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Validator;

class Forgot extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Forgot
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
                'value' => 'Submit'
            ]
        ];

        parent::__construct($fields, $action, $method);

        $this->setAttribute('id', 'forgot-form');
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
            }
        }

        return $this;
    }

}