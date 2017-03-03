<?php
/**
 * Phire CMS role form configuration
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
        'role_parent_id' => [
            'type'       => 'select',
            'label'      => 'Parent',
            'values'     => null
        ],
        'id' => [
            'type'  => 'hidden',
            'value' => '0'
        ]
    ],
    [
        'name' => [
            'type'       => 'text',
            'label'      => 'Name',
            'required'   => 'true',
            'attributes' => [
                'size'   => 60,
                'style'  => 'width: 99.5%',
                'class'  => 'form-control'
            ]
        ]
    ],
    [
        'resource_1' => [
            'type'       => 'select',
            'label'      => '<a href="#" id="permission-add-link">[+]</a> Resources, Actions &amp; Permissions',
            'values'     => null
        ],
        'action_1' => [
            'type'       => 'select',
            'values'     => ['----' => '----']
        ],
        'permission_1' => [
            'type'     => 'select',
            'values'   => [
                '----' => '----',
                '0'    => 'deny',
                '1'    => 'allow'
            ]
        ]
    ]
];

