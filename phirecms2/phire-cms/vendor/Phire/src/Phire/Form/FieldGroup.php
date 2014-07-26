<?php
/**
 * @namespace
 */
namespace Phire\Form;

use Pop\Validator;

class FieldGroup extends AbstractForm
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
            'name' => array(
                'type'       => 'text',
                'label'      => $this->i18n->__('Name &amp; Order'),
                'required'   => true,
                'attributes' => array(
                    'size'  => 40,
                    'style' => 'width: 376px;'
                )
            ),
            'order' => array(
                'type'       => 'text',
                'attributes' => array('size' => 3),
                'value'      => 0
            ),
            'dynamic' => array(
                'type'  => 'radio',
                'label' => $this->i18n->__('Dynamic') . '?',
                'value' => array(
                    '0' => $this->i18n->__('No'),
                    '1' => $this->i18n->__('Yes')
                ),
                'marked' => '0'
            ),
            'id' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'update_value' => array(
                'type'  => 'hidden',
                'value' => 0
            ),
            'submit' => array(
                'type'  => 'submit',
                'label' => '&nbsp;',
                'value' => $this->i18n->__('SAVE'),
                'attributes' => array(
                    'class' => 'save-btn',
                    'style' => 'width: 190px;'
                )
            ),
            'update' => array(
                'type'       => 'button',
                'value'      => $this->i18n->__('UPDATE'),
                'attributes' => array(
                    'onclick' => "return phire.updateForm('#field-group-form', false);",
                    'class' => 'update-btn',
                    'style' => 'width: 190px;'
                )
            )
        );

        if (strpos($_SERVER['REQUEST_URI'], '/edit/') !== false) {
            $this->initFieldsValues['name']['attributes']['onkeyup'] = "phire.updateTitle('#field-group-title', this);";
        }

        $this->setAttributes('id', 'field-group-form');
    }

}

