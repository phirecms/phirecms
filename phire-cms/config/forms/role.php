<?php
/**
 * Pop Web Bootstrap Application Framework role form configuration
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
            'value'      => null
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
            'value'      => null
        ],
        'action_1' => [
            'type'       => 'select',
            'value'      => ['----' => '----']
        ],
        'permission_1' => [
            'type'     => 'select',
            'value'    => [
                '----' => '----',
                '0'    => 'deny',
                '1'    => 'allow'
            ]
        ]
    ]
];

