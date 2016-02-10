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
            'validators' => new \Pop\Validator\LengthGte(6)
        ],
        'password2' => [
            'type'      => 'password',
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
            'validators' => new \Pop\Validator\Email()
        ],
        'phone' => [
            'type'     => 'text',
            'label'    => 'Phone'
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'value' => 'Save',
            'attributes' => [
                'class'  => 'save-btn'
            ]
        ],
        'role_id' => [
            'type'  => 'hidden',
            'value' => '0'
        ],
        'id' => [
            'type'  => 'hidden',
            'value' => '0'
        ]
    ]
];

