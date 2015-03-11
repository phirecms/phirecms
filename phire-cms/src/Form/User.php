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
     * @param  int    $rid
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return User
     */
    public function __construct($rid, array $fields, $action = null, $method = 'post')
    {
        $role = Table\Roles::findById($rid);

        if ($role->email_as_username) {
            unset($fields[1]['username']);
        }

        $fields[0]['role_id']['value'] = $rid;

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

        if (($_POST) && (null !== $this->email1)) {
            // Check for dupe username
            $user = null;
            if (null !== $this->username) {
                $user = Table\Users::findBy(['username' => $this->username]);
                if (isset($user->id) && ($this->id != $user->id)) {
                    $this->getElement('username')
                         ->addValidator(new Validator\NotEqual($this->username, 'That username already exists.'));
                }
            }

            // Check for dupe email
            $email = Table\Users::findBy(['email' => $this->email1]);
            if (isset($email->id) && ($this->id != $email->id)) {
                $this->getElement('email1')
                     ->addValidator(new Validator\NotEqual($this->email1, 'That email already exists.'));
            }

            // If existing user
            if ((int)$_POST['id'] > 0) {
                if (((null !== $user) && ($user->email !== $this->email1)) && ($email->email !== $this->email1)) {
                    $this->getElement('email2')
                         ->setRequired(true)
                         ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
                }
                if (!empty($this->password1)) {
                    $this->getElement('password2')
                         ->setRequired(true)
                         ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
                }
            // Else, if new user, check email and password matches
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