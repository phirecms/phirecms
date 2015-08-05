<?php

namespace Phire\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Form\Element;
use Pop\Validator;

class UserEmail extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return UserEmail
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'user-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return UserEmail
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && (null !== $this->email)) {
            // Check for dupe email
            $user  = null;
            $email = null;
            if (null !== $this->email) {
                $user = Table\Users::findBy(['username' => $this->email]);
                if (isset($user->id) && ($this->id != $user->id)) {
                    $this->getElement('email')
                         ->addValidator(new Validator\NotEqual($this->email, 'That email already exists.'));
                } else {
                    $email = Table\Users::findBy(['email' => $this->email]);
                    if (isset($email->id) && ($this->id != $email->id)) {
                        $this->getElement('email')
                             ->addValidator(new Validator\NotEqual($this->email1, 'That email already exists.'));
                    }
                }
            }

            // If existing user
            if ((int)$_POST['id'] > 0) {
                if (!empty($this->password1)) {
                    $this->getElement('password2')
                         ->setRequired(true)
                         ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
                }
            // Else, if new user, check email and password matches
            } else {
                $this->getElement('password2')
                     ->setRequired(true)
                     ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
            }
        }

        return $this;
    }

}