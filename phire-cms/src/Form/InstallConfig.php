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
    public function __construct($config, array $fields, $action = null, $method = 'post')
    {
        $fields[0]['config']['value'] = $config;

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