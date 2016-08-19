<?php
/**
 * Pop Web Bootstrap Application Framework user form configuration
 */
return [
    [
        'submit' => [
            'type'       => 'submit',
            'value'      => 'Save',
            'attributes' => [
                'class'  => 'btn btn-md btn-info btn-block text-uppercase'
            ]
        ],
        'active' => [
            'type'      => 'radio',
            'label'     => 'Active',
            'value' => [
                '1' => 'Yes',
                '0' => 'No'
            ],
            'marked' => 0
        ],
        'verified' => [
            'type'      => 'radio',
            'label'     => 'Verified',
            'value' => [
                '1' => 'Yes',
                '0' => 'No'
            ],
            'marked' => 0
        ],
        'clear_logins'   => [
            'type'  => 'checkbox',
            'label' => 'Clear Logins?',
            'value' => [
                1 => ''
            ]
        ],
        'failed_attempts'   => [
            'type'  => 'text',
            'label' => 'Failed Attempts',
            'value' => 0,
            'attributes' => [
                'class' => 'form-control input-sm',
                'size'  => 3
            ]
        ],
        'role_id'   => [
            'type'  => 'hidden',
            'value' => 0
        ],
        'id' => [
            'type'  => 'hidden',
            'value' => '0'
        ]
    ],
    [
        'username' => [
            'type'     => 'text',
            'label'    => 'Username',
            'required' => true,
            'attributes' => [
                'size'    => 40,
                'class'   => 'form-control'
            ]
        ],
        'password1' => [
            'type'       => 'password',
            'label'      => 'Password',
            'required'   => true,
            'validators' => new \Pop\Validator\LengthGte(6),
            'attributes' => [
                'size'    => 40,
                'class'   => 'form-control'
            ]
        ],
        'password2' => [
            'type'       => 'password',
            'label'      => 'Re-Type Password',
            'required'   => true,
            'attributes' => [
                'size'    => 40,
                'class'   => 'form-control'
            ]
        ],
        'email' => [
            'type'       => 'email',
            'label'      => 'Email',
            'attributes' => [
                'size'    => 40,
                'class'   => 'form-control'
            ]
        ]
    ]
];

