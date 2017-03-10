<?php
/**
 * Phire CMS install user form configuration
 */
return [
    [
        'username' => [
            'type'     => 'text',
            'label'    => 'Username',
            'required' => true,
            'attributes' => [
                'class' => 'form-control'
            ]
        ],
        'password1' => [
            'type'       => 'password',
            'label'      => 'Password',
            'required'   => true,
            'validators' => new \Pop\Validator\LengthGte(6, 'The password must be at least 6 characters.'),
            'attributes' => [
                'class' => 'form-control'
            ]
        ],
        'password2' => [
            'type'      => 'password',
            'required'  => true,
            'label'     => 'Re-Type Password',
            'attributes' => [
                'class' => 'form-control'
            ]
        ],
        'email' => [
            'type'       => 'email',
            'label'      => 'Email',
            'required'   => true,
            'validators' => new \Pop\Validator\Email(),
            'attributes' => [
                'class' => 'form-control'
            ]
        ]
    ],
    [
        'active' => [
            'type'  => 'hidden',
            'value' => 1
        ],
        'verified' => [
            'type'  => 'hidden',
            'value' => 1
        ],
        'role_id' => [
            'type'  => 'hidden',
            'value' => 2001
        ],
        'submit' => [
            'type'  => 'submit',
            'value' => 'Submit',
            'attributes' => [
                'class'  => 'btn btn-lg btn-info btn-block text-uppercase'
            ]
        ]
    ]
];

