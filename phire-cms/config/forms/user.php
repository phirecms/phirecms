<?php
/**
 * Phire CMS user form configuration
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
            'values' => [
                '1' => 'Yes',
                '0' => 'No'
            ],
            'checked' => 0
        ],
        'verified' => [
            'type'      => 'radio',
            'label'     => 'Verified',
            'values' => [
                '1' => 'Yes',
                '0' => 'No'
            ],
            'checked' => 0
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
            'attributes' => [
                'size'    => 40,
                'class'   => 'form-control'
            ]
        ],
        'password2' => [
            'type'       => 'password',
            'label'      => 'Re-Type Password',
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

