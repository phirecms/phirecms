<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Validator;
use Phire\Table;

class Login extends AbstractForm
{

    /**
     * Constructor method to instantiate the form object
     *
     * @param  string $action
     * @param  string $method
     * @return self
     */
    public function __construct($action = null, $method = 'post')
    {
        parent::__construct($action, $method, null, '        ');

        $this->initFieldsValues = array(
            'username' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Username'),
                'required'   => true,
                'attributes' => array(
                    'size'  => 30,
                    'style' => 'display: block; width: 290px; margin: 0 auto;'
                )
            ),
            'password' => array(
                'type'       => 'password',
                'label'      => $this->i18n->__('Password'),
                'required'   => true,
                'attributes' => array(
                    'size'  => 30,
                    'style' => 'display: block; width: 290px; margin: 0 auto;'
                )
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => $this->i18n->__('LOGIN'),
                'attributes' => array(
                    'class' => 'update-btn',
                    'style' => 'display: block; width: 300px; margin: 0 auto;'
                )
            )
        );


        $this->setAttributes('id', 'login-form');
    }

    /**
     * Set the field values
     *
     * @param  array                  $values
     * @param  array                  $filters
     * @param  \Phire\Auth\Auth       $auth
     * @param  \Phire\Table\UserTypes $type
     * @param  \Phire\Model\User      $user
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null, $auth = null, $type = null, $user = null)
    {
        parent::setFieldValues($values, $filters);

        if ($_POST) {
            // Authenticate and get the auth result
            $auth->authenticate($this->username, $this->password);
            $result = $auth->getAuthResult($type, $this->username);

            if (null !== $result) {
                $user->login($this->username, $type, false);
                if ($auth->getResult() == \Pop\Auth\Auth::PASSWORD_INCORRECT) {
                    $this->getElement('password')
                         ->addValidator(new Validator\NotEqual($this->password, $result));
                } else {
                    $this->getElement('username')
                         ->addValidator(new Validator\NotEqual($this->username, $result));
                }
            }

            // Check the user's allowed sites
            if (strtolower($type->type) != 'user') {
                $u = Table\Users::findBy(array('username' => $this->username));
                if (isset($u->id)) {
                    $siteIds = unserialize($u->site_ids);
                    $site = Table\Sites::findBy(array('document_root' => $_SERVER['DOCUMENT_ROOT']));
                    $siteId = (isset($site->id)) ? $site->id : '0';
                    if (!in_array($siteId, $siteIds)) {
                        $this->getElement('username')
                             ->addValidator(new Validator\NotEqual($this->username, $this->i18n->__('That user is not allowed on this site.')));
                    }
                }
            }
        }

        return $this;
    }

}

