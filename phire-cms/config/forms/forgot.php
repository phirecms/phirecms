<?php
/**
 * Phire\Form\Forgot configuration
 */
return [
    [
        'email' => [
            'type'       => 'email',
            'label'      => 'Forgot Your Password?',
            'required'   => 'true',
            'validators' => new \Pop\Validator\Email(),
            'attributes' => [
                'placeholder' => 'Please enter your email'
            ]
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'value' => 'Submit',
            'attributes' => [
                'class'  => 'save-btn'
            ]
        ]
    ]
];

