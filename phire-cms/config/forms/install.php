<?php
/**
 * Phire\Form\Install configuration
 */
return [
    [
        'db_adapter'     => [
            'type'       => 'select',
            'label'      => 'DB Adapter',
            'value'      => null,
            'attributes' => [
                'onchange' => 'phire.changeDbAdapter(this);'
            ]
        ],
        'db_name'   => [
            'type'  => 'text',
            'label' => 'DB Name'
        ],
        'db_username' => [
            'type'    => 'text',
            'label'   => 'DB Username'
        ],
        'db_password' => [
            'type'    => 'text',
            'label'   => 'DB Password'
        ],
        'db_host'   => [
            'type'  => 'text',
            'label' => 'DB Host',
            'value' => 'localhost'
        ],
        'db_prefix' => [
            'type'  => 'text',
            'name'  => 'db_prefix',
            'label' => 'DB Table Prefix',
            'value' => 'ph_'
        ],
        'app_uri'   => [
            'type'  => 'text',
            'label' => 'Application URI',
            'value' => APP_URI
        ],
        'content_path' => [
            'type'     => 'text',
            'label'    => 'Content Path',
            'required' => true,
            'value'    => CONTENT_PATH
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'value' => 'Continue',
            'attributes' => [
                'class'  => 'save-btn'
            ]
        ]
    ]
];

