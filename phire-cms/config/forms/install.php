<?php
/**
 * Phire CMS install form configuration
 */
return [
    [
        'db_adapter'     => [
            'type'       => 'select',
            'label'      => 'DB Adapter',
            'value'      => null,
            'attributes' => [
                'onchange' => 'phire.changeDbAdapter(this);',
                'class' => 'form-control pop-select'
            ]
        ],
        'db_name'   => [
            'type'  => 'text',
            'label' => 'DB Name',
            'attributes' => [
                'class' => 'form-control'
            ]
        ],
        'db_username' => [
            'type'    => 'text',
            'label'   => 'DB Username',
            'attributes' => [
                'class' => 'form-control'
            ]
        ],
        'db_password' => [
            'type'    => 'text',
            'label'   => 'DB Password',
            'attributes' => [
                'class' => 'form-control'
            ]
        ],
        'db_host'   => [
            'type'  => 'text',
            'label' => 'DB Host',
            'value' => 'localhost',
            'attributes' => [
                'class'  => 'form-control'
            ]
        ],
        'db_prefix' => [
            'type'  => 'text',
            'name'  => 'db_prefix',
            'label' => 'DB Table Prefix',
            'value' => 'ph_',
            'attributes' => [
                'class'  => 'form-control'
            ]
        ],
        'app_uri'   => [
            'type'     => 'text',
            'label'    => 'Application URI',
            'required' => true,
            'value'    => APP_URI,
            'attributes' => [
                'class'  => 'form-control'
            ]
        ],
        'content_path' => [
            'type'     => 'text',
            'label'    => 'Content Path',
            'required' => true,
            'value'    => CONTENT_PATH,
            'attributes' => [
                'class'  => 'form-control'
            ]
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'value' => 'Continue',
            'attributes' => [
                'class'  => 'btn btn-lg btn-info btn-block text-uppercase'
            ]
        ]
    ]
];

