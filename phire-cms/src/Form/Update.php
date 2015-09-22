<?php

namespace Phire\Form;

use Pop\Form\Form;
use Pop\Validator;

class Update extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Update
     */
    public function __construct(array $fields, $action = null, $method = 'post')
    {
        parent::__construct($fields, $action, $method);
        $this->setAttribute('id', 'update-form');
        $this->setIndent('    ');
    }

}