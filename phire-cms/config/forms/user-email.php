<?php

return [
    [
        'submit' => [
            'type'       => 'submit',
            'value'      => 'Save',
            'attributes' => [
                'class'  => 'save-btn wide'
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
        'email' => [
            'type'       => 'text',
            'label'      => 'Email',
            'required'   => true,
            'validators' => new \Pop\Validator\Email(),
            'attributes' => [
                'size'    => 40
            ]
        ],
        'password1' => [
            'type'       => 'password',
            'label'      => 'Password',
            'required'   => true,
            'validators' => new \Pop\Validator\LengthGte(6),
            'attributes' => [
                'size'    => 40
            ]
        ],
        'password2' => [
            'type'       => 'password',
            'label'      => 'Re-Type Password',
            'required'   => true,
            'attributes' => [
                'size'    => 40
            ]
        ]
    ],
    [
        'first_name' => [
            'type'     => 'text',
            'label'    => 'First Name',
            'attributes' => [
                'size'    => 40
            ]
        ],
        'last_name' => [
            'type'     => 'text',
            'label'    => 'Last Name',
            'attributes' => [
                'size'    => 40
            ]
        ],
        'company' => [
            'type'     => 'text',
            'label'    => 'Company',
            'attributes' => [
                'size'    => 40
            ]
        ],
        'phone' => [
            'type'     => 'text',
            'label'    => 'Phone',
            'attributes' => [
                'size'    => 40
            ]
        ]
    ]
];

