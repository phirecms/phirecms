<?php

namespace Phire\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Validator;

class Profile extends Form
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
     * @return Profile
     */
    public function __construct($rid, array $fields, $action = null, $method = 'post')
    {
        $role = Table\UserRoles::findById($rid);

        if ($role->email_as_username) {
            unset($fields[0]['username']);
        }

        $fields[1]['role_id']['value'] = $rid;

        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'profile-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return Profile
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
                         ->addValidator(new Validator\NotEqual($this->username, 'That username is not allowed.'));
                }
            }

            // Check for dupe email
            $email = Table\Users::findBy(['email' => $this->email1]);
            if (isset($email->id) && ($this->id != $email->id)) {
                $this->getElement('email1')
                     ->addValidator(new Validator\NotEqual($this->email1, 'That email is not allowed.'));
            }

            // Check email matches
            if (((null !== $user) && ($user->email !== $this->email1)) && ($email->email !== $this->email1)) {
                $this->getElement('email2')
                     ->setRequired(true)
                     ->addValidator(new Validator\Equal($this->email1, 'The emails do not match.'));
            }

            // Check password matches
            if (!empty($this->password1)) {
                $this->getElement('password2')
                     ->setRequired(true)
                     ->addValidator(new Validator\Equal($this->password1, 'The passwords do not match.'));
            }
        }

        return $this;
    }

}