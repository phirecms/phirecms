<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Validator;
use Phire\Table;

class Forgot extends AbstractForm
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
            'email' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Email'),
                'required'   => true,
                'attributes' => array(
                    'size'  => 30,
                    'style' => 'display: block; width: 288px; margin: 0 auto;'
                ),
                'validators' => new Validator\Email()
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => $this->i18n->__('SUBMIT'),
                'attributes' => array(
                    'class' => 'update-btn',
                    'style' => 'display: block; width: 300px; margin: 0 auto;'
                )
            )
        );

        $this->setAttributes('id', 'forgot-form');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @param  array $filters
     * @return \Pop\Form\Form
     */
    public function setFieldValues(array $values = null, $filters = null)
    {
        parent::setFieldValues($values, $filters);

        if ($_POST) {
            if (Validator\Email::factory()->evaluate($this->email)) {
                $user = Table\Users::findBy(array('email' => $this->email));
                if (!isset($user->id)) {
                    $this->getElement('email')
                         ->addValidator(new Validator\NotEqual($this->email, $this->i18n->__('That email does not exist.')));
                }
            }
        }

        return $this;
    }

}

