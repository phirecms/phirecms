<?php

return [
    [
        'username' => [
            'type'     => 'text',
            'label'    => 'Username',
            'required' => true
        ],
        'password1' => [
            'type'       => 'password',
            'label'      => 'Password',
            'required'   => true,
            'validators' => new \Pop\Validator\LengthGte(6)
        ],
        'password2' => [
            'type'      => 'password',
            'required'  => true,
            'label'     => 'Re-Type Password'
        ]
    ],
    [
        'first_name' => [
            'type'     => 'text',
            'label'    => 'First Name'
        ],
        'last_name' => [
            'type'     => 'text',
            'label'    => 'Last Name'
        ],
        'company' => [
            'type'     => 'text',
            'label'    => 'Company'
        ],
        'title' => [
            'type'     => 'text',
            'label'    => 'Title'
        ],
        'email' => [
            'type'       => 'email',
            'label'      => 'Email',
            'required'   => false,
            'validators' => new \Pop\Validator\Email()
        ],
        'phone' => [
            'type'     => 'text',
            'label'    => 'Phone'
        ]
    ],
    [
        'role_id' => [
            'type'  => 'hidden',
            'value' => '0'
        ],
        'submit' => [
            'type'  => 'submit',
            'value' => 'Register',
            'attributes' => [
                'class'  => 'save-btn'
            ]
        ]
    ]
];

