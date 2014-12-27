<?php

namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;

class InstallConfig extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  string $config
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return InstallConfig
     */
    public function __construct($config, array $fields = null, $action = null, $method = 'post')
    {
        $fields = [
            [
                'config' => [
                    'type'       => 'textarea',
                    'label'      => 'Configuration',
                    'required'   => true,
                    'value'      => $config,
                    'attributes' => [
                        'rows'    => 50,
                        'cols'    => 120,
                        'style'   => 'width: 98%; height: 420px; display: block;',
                        'onfocus' => 'this.select();'
                    ]
                ]
            ],
            [
                'submit' => [
                    'type'  => 'submit',
                    'value' => 'Continue',
                    'attributes' => [
                        'class'  => 'save-btn',
                        'style'  => 'float: right; margin: 0 15px 0 0;'
                    ]
                ]
            ]
        ];

        parent::__construct($fields, $action, $method);

        $this->setAttribute('id', 'install-config-form');
        $this->setIndent('    ');
    }

    /**
     * Set the field values
     *
     * @param  array $values
     * @return InstallConfig
     */
    public function setFieldValues(array $values = null)
    {
        parent::setFieldValues($values);

        if (($_POST) && !empty($this->config)) {
            if ((DB_INTERFACE == '') || (DB_NAME == '')) {
                $this->getElement('config')->addValidator(
                    new Validator\NotEqual($this->config,'The configuration file has not been written yet.')
                );
            }
        }

        return $this;
    }

}