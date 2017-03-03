<?php
/**
 * Phire CMS login form configuration
 */
return [
    [
        'username' => [
            'type'       => 'text',
            'required'   => 'true',
            'attributes' => [
                'placeholder' => 'Username',
                'class'       => 'form-control'
            ]
        ],
        'password' => [
            'type'       => 'password',
            'required'   => 'true',
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

