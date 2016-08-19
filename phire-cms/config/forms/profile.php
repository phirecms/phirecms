<?php
/**
 * Pop Web Bootstrap Application Framework profile form configuration
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
            'label'      => 'Change Password?',
            'validators' => new \Pop\Validator\LengthGte(6),
            'attributes' => [
                'class' => 'form-control'
            ]
        ],
        'password2' => [
            'type'      => 'password',
            'label'     => 'Re-Type Password',
            'attributes' => [
                'class' => 'form-control'
            ]
        ]
    ],
    [
        'email' => [
            'type'       => 'email',
            'label'      => 'Email',
            'validators' => new \Pop\Validator\Email(),
            'attributes' => [
                'class' => 'form-control'
            ]
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'value' => 'Save',
            'attributes' => [
                'class'  => 'btn btn-lg btn-info btn-block text-uppercase'
            ]
        ],
        'id' => [
            'type'  => 'hidden',
            'value' => '0'
        ]
    ]
];
