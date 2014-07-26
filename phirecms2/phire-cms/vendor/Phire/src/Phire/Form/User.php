<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Validator;
use Phire\Table;

class User extends AbstractForm
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string         $action
     * @param  string         $method
     * @param  int            $tid
     * @param  boolean        $profile
     * @param  int            $uid
     * @param \Phire\Auth\Acl $acl
     * @param  boolean        $register
     * @return self
     */
    public function __construct($action = null, $method = 'post', $tid = 0, $profile = false, $uid = 0, $acl = null, $register = false)
    {
        parent::__construct($action, $method, null, '        ');

        // Create user type fields/form first
        if ($tid == 0) {
            $typesAry = array();
            $types = Table\UserTypes::findAll('id ASC');
            foreach ($types->rows as $type) {
                if ($acl->isAuth('Phire\Controller\Phire\User\IndexController', 'add_' . $type->id)) {
                    $typesAry[$type->id] = $type->type;
                }
            }
            $this->initFieldsValues = array(
                'type_id' => array(
                    'type'     => 'select',
                    'required' => true,
                    'label'    => $this->i18n->__('Select User Type'),
                    'value'    => $typesAry,
                    'attributes' => array(
                        'style' => 'margin: 0 10px 0 0; padding: 6px 5px 7px 5px; height: 32px;'
                    )
                ),
                'submit' => array(
                    'type'  => 'submit',
                    'value' => $this->i18n->__('SELECT'),
                    'attributes' => array(
                        'class'   => 'save-btn',
                        'style' => 'margin: 0; padding: 5px 6px 6px 6px; width: 100px; height: 32px;'
                    )
                )
            );
            $id = 'user-select-form';
        // Else, create initial user fields
        } else {
            $this->initFieldsValues = $this->getInitFields($tid, $profile, $uid, $action, $register);
            if (strpos($action, '/install/user') !== false) {
                $id = 'user-install-form';
            } else if ($profile) {
                $id = 'user-install-form';
            } else {
                $id = 'user-form';
            }
        }

        $this->setAttributes('id', $id);
    }

    /**
     * Set the field values
     *
     * @param  array       $values
     * @param  array       $filters
     * @param  \Pop\Config $config
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null, $config = null)
    {
        parent::setFieldValues($values, $filters);

        if ($this->id != 0) {
            if (null !== $this->getElement('email2')) {
                $this->getElement('email2')->setRequired(false);
            }
            if ((null !== $this->getElement('password1')) && (null === $this->reset_pwd)) {
                $this->getElement('password1')->setRequired(false);
                $this->getElement('password2')->setRequired(false);
            }
        }

        // Add validators for checking dupe usernames
        // and matching the emails and passwords
        if (($_POST) && isset($_POST['id'])) {
            if (isset($this->fields['username'])) {
                $username =  $this->username;
                $usernameField = 'username';
            } else {
                $username =  $this->email1;
                $usernameField = 'email1';
            }

            $user = Table\Users::findBy(array('username' => $username));
            if (isset($user->id) && ($this->id != $user->id)) {
                $this->getElement($usernameField)
                     ->addValidator(new Validator\NotEqual($username, $this->i18n->__('That user already exists.')));
            }

            $email = Table\Users::findBy(array('email' => $this->email1));
            if (isset($email->id) && ($this->id != $email->id)) {
                $this->getElement('email1')
                     ->addValidator(new Validator\NotEqual($this->email1, $this->i18n->__('That email already exists.')));
            }

            if (null !== $this->getElement('email2')) {
                $this->getElement('email2')
                     ->addValidator(new Validator\Equal($this->email1, $this->i18n->__('The emails do not match.')));
            }

            // If the password fields are set, check them for a match
            if (isset($this->password2)) {
                $this->getElement('password2')
                     ->addValidator(new Validator\Equal($this->password1, $this->i18n->__('The passwords do not match.')));
            }

            if ($this->reset_pwd) {
                $user = Table\Users::findById($this->id);
                if (isset($user->id)) {
                    $curPassword = $user->password;
                    $type = Table\UserTypes::findById($user->type_id);
                    if (isset($type->id)) {
                        $encOptions = $config->encryptionOptions->asArray();
                        $auth = new \Pop\Auth\Adapter\Table('Phire\Table\Users');
                        $result = $auth->authenticate($this->username, $this->password2, $type->password_encryption, $encOptions);
                        if ($result != \Pop\Auth\Auth::PASSWORD_INCORRECT) {
                            $this->getElement('password2')
                                 ->addValidator(new Validator\Equal($curPassword, $this->i18n->__('The new password cannot be the same.')));
                        }
                    }
                }
            }
        }

        $this->checkFiles();

        return $this;
    }

    /**
     * Get the init field values
     *
     * @param  int     $tid
     * @param  boolean $profile
     * @param  int     $uid
     * @param  string  $action
     * @param  boolean $register
     * @return array
     */
    protected function getInitFields($tid = 0, $profile = false, $uid = 0, $action, $register = false)
    {
        $type    = Table\UserTypes::findById($tid);
        $fields1 = array();

        // Continue setting up initial user fields
        $fields1['email1'] = array(
            'type'       => 'text',
            'label'      => $this->i18n->__('Email'),
            'required'   => true,
            'attributes' => array('size' => 30),
            'validators' => new Validator\Email()
        );
        if ($type->email_verification) {
            $fields1['email2'] = array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Re-Type Email'),
                'required'   => true,
                'attributes' => array('size' => 30),
                'validators' => new Validator\Email()
            );
        }

        // If not email as username, create username field
        if (!$type->email_as_username) {
            $fields2 = array(
                'username' => array(
                    'type'       => 'text',
                    'label'      => $this->i18n->__('Username'),
                    'required'   => true,
                    'attributes' => array('size' => 30),
                    'validators' => array(
                        new Validator\AlphaNumeric(),
                        new Validator\LengthGte(4)
                    )
                )
            );
            if ($uid != 0) {
                $fields2['username']['attributes']['onkeyup'] = "phire.updateTitle('#username-title', this);";
            }
        } else {
            $fields2 = array();

            if ($uid != 0) {
                $fields1['email1']['attributes']['onkeyup'] = "phire.updateTitle('#username-title', this);";
            }
        }

        // Continue setting up initial user fields
        if ($type->login) {
            $fields3 = array(
                'password1' => array(
                    'type'       => 'password',
                    'label'      => $this->i18n->__('Enter Password'),
                    'required'   => true,
                    'attributes' => array('size' => 30),
                    'validators' => new Validator\LengthGte(6)
                ),
                'password2' => array(
                    'type'       => 'password',
                    'label'      => $this->i18n->__('Re-Type Password'),
                    'required'   => true,
                    'attributes' => array('size' => 30),
                    'validators' => new Validator\LengthGte(6)
                )
            );
        } else {
            $fields3 = array();
        }

        $fieldGroups = array();
        $dynamicFields = false;

        $model = str_replace('Form', 'Model', get_class($this));
        $newFields = \Phire\Model\Field::getByModel($model, $tid, $uid);
        if ($newFields['dynamic']) {
            $dynamicFields = true;
        }
        if ($newFields['hasFile']) {
            $this->hasFile = true;
        }
        foreach ($newFields as $key => $value) {
            if (is_numeric($key)) {
                $fieldGroups[] = $value;
            }
        }

        $fields4 = array();

        if ($register) {
            $site = Table\Sites::getSite();
            if ($type->use_csrf) {
                $fields4['csrf'] = array(
                    'type'  => 'csrf',
                    'value' => \Pop\Filter\String::random(8)
                );
            }
            if ($type->use_captcha) {
                $fields4['captcha'] = array(
                    'type'       => 'captcha',
                    'label'      => $this->i18n->__('Enter Code'),
                    'captcha'    => '<br /><img id="captcha-image" src="' . $site->base_path . '/captcha" /><br /><a class="reload-link" href="#" onclick="document.getElementById(\'captcha-image\').src = \'' . $site->base_path . '/captcha?reload=1\';return false;">' . $this->i18n->__('Reload') . '</a>',
                    'attributes' => array('size' => 5)
                );
            }
        }

        // Finish the initial fields
        $fields4['submit'] = array(
            'type'  => 'submit',
            'value' => (strpos($action, '/register') !== false) ? $this->i18n->__('REGISTER') : $this->i18n->__('SAVE'),
            'attributes' => array(
                'class' => ((strpos($action, '/install/user') !== false) || ($profile)) ? 'update-btn' : 'save-btn'
            )
        );

        if ($profile) {
            $fields4['submit']['label'] = '&nbsp;';
            $fields4['submit']['attributes']['style'] = 'width: 250px;';
            $fields4['profile'] = array(
                'type'  => 'hidden',
                'value' => 1
            );
            $sess = \Pop\Web\Session::getInstance();
            if (isset($sess->reset_pwd)) {
                $fields4['reset_pwd'] = array(
                    'type'  => 'hidden',
                    'value' => 1
                );
            }
        }

        if (!$profile) {
            $fields4['update'] = array(
                'type'       => 'button',
                'value'      => $this->i18n->__('Update'),
                'attributes' => array(
                    'onclick' => "return phire.updateForm('#user-form', " . ((($this->hasFile) || ($dynamicFields)) ? 'true' : 'false') . ");",
                    'class' => 'update-btn'
                )
            );
        }

        $fields4['type_id'] = array(
            'type'  => 'hidden',
            'value' => $tid
        );
        $fields4['id'] = array(
            'type'  => 'hidden',
            'value' => 0
        );

        if (!$profile) {
            $fields4['update_value'] = array(
                'type'  => 'hidden',
                'value' => 0
            );
        }

        // If not profile
        if (!$profile) {
            // Get roles for user type
            $rolesAry = array('0' => '(' . $this->i18n->__('Blocked') . ')');

            if ($tid != 0) {
                $roles = Table\UserRoles::findBy(array('type_id' => $tid), 'id ASC');
                foreach ($roles->rows as $role) {
                    $rolesAry[$role->id] = $role->name;
                }
            }

            $siteIds = array('0' => $_SERVER['HTTP_HOST']);

            $sites = Table\Sites::findAll();
            foreach ($sites->rows as $site) {
                $siteIds[(string)$site->id] = $site->domain;
            }

            $fields4['role_id'] = array(
                'type'     => 'select',
                'required' => true,
                'label'    => $this->i18n->__('User Role'),
                'value'    => $rolesAry,
                'marked'   => $type->default_role_id
            );

            $fields4['verified'] = array(
                'type'   => 'select',
                'label'  => $this->i18n->__('Verified'),
                'value'  => array('1' => $this->i18n->__('Yes'), '0' => $this->i18n->__('No')),
                'marked' => '0'
            );
            $fields4['failed_attempts'] = array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Failed Attempts'),
                'attributes' => array('size' => 3)
            );
            $fields4['site_ids'] = array(
                'type'  => 'checkbox',
                'label' => $this->i18n->__('Allowed Sites'),
                'value' => $siteIds
            );
        }

        if ((strpos($action, '/install/user') !== false) || ($profile)) {
            $allFields = array($fields1, $fields2, $fields3);
            if (count($fieldGroups) > 0) {
                foreach ($fieldGroups as $fg) {
                    $allFields[] = $fg;
                }
            }
            $allFields[] = $fields4;
        } else {
            $allFields = array($fields4, $fields1, $fields2, $fields3);
            if (count($fieldGroups) > 0) {
                foreach ($fieldGroups as $fg) {
                    $allFields[] = $fg;
                }
            }
        }

        return $allFields;
    }

}

