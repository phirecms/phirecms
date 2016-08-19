<?php
/**
 * Pop Web Bootstrap Application Framework login form configuration
 */
return [
    [
        'username' => [
            'type'       => 'text',
            'required'   => 'true',
            'validators' => new \Pop\Validator\NotEmpty(),
            'attributes' => [
                'placeholder' => 'Username',
                'class'       => 'form-control'
            ]
        ],
        'password' => [
            'type'       => 'password',
            'required'   => 'true',
            'validators' => new \Pop\Validator\NotEmpty(),
            'attributes' => [
                'placeholder' => 'Password',
                'class'       => 'form-control'
            ]
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'value' => 'Login',
            'attributes' => [
                'class'  => 'btn btn-lg btn-info btn-block text-uppercase'
            ]
        ]
    ]
];

