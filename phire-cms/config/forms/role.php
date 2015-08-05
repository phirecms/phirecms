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
        'role_parent_id' => [
            'type'       => 'select',
            'label'      => 'Parent',
            'value'      => null,
            'attributes' => [
                'class'    => 'wide'
            ]
        ],
        'verification' => [
            'type'      => 'radio',
            'label'     => 'Verification',
            'value'     => [
                '1' => 'Yes',
                '0' => 'No'
            ],
            'marked' => 0
        ],
        'approval' => [
            'type'      => 'radio',
            'label'     => 'Approval',
            'value'     => [
                '1' => 'Yes',
                '0' => 'No'
            ],
            'marked' => 0
        ],
        'email_as_username' => [
            'type'      => 'radio',
            'label'     => 'Email as Username',
            'value'     => [
                '1' => 'Yes',
                '0' => 'No'
            ],
            'marked' => 0
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
                'size'    => 60,
                'style'   => 'width: 99.5%',
                'onkeyup' => 'phire.changeTitle(this.value);'
            ]
        ]
    ],
    [
        'resource_1' => [
            'type'       => 'select',
            'label'      => '<a href="#" onclick="return phire.addResource();">[+]</a> Resources, Actions &amp; Permissions',
            'value'      => null,
            'attributes' => [
                'onchange' => 'phire.changeActions(this);'
            ]
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

