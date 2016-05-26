<?php
/**
 * Phire\Form\Login configuration
 */
return [
    [
        'username' => [
            'type'       => 'text',
            'required'   => 'true',
            'validators' => new \Pop\Validator\NotEmpty(),
            'attributes' => [
                'placeholder' => 'Username'
            ]
        ],
        'password' => [
            'type'       => 'password',
            'required'   => 'true',
            'validators' => new \Pop\Validator\NotEmpty(),
            'attributes' => [
                'placeholder' => 'Password'
            ]
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'value' => 'Login',
            'attributes' => [
                'class'  => 'save-btn'
            ]
        ]
    ]
];

