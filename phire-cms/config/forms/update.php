<?php

return [
    [
        'ftp_address' => [
            'type'       => 'text',
            'label'      => 'FTP Address',
            'required'   => 'true',
            'attributes' => [
                'size' => '40'
            ]
        ],
        'ftp_username' => [
            'type'       => 'text',
            'label'      => 'FTP Username',
            'required'   => 'true',
            'attributes' => [
                'size' => '40'
            ]
        ],
        'ftp_password' => [
            'type'       => 'text',
            'label'      => 'FTP Password',
            'required'   => 'true',
            'attributes' => [
                'size' => '40'
            ]
        ],
        'ftp_root' => [
            'type'       => 'text',
            'label'      => 'FTP Root',
            'attributes' => [
                'size'        => '40',
                'placeholder' => 'Directory to change to, i.e. httpdocs'
            ]
        ],
        'use_pasv' => [
            'type'  => 'radio',
            'label' => 'Use PASV',
            'value' => [
                '1' => 'Yes',
                '0' => 'No'
            ],
            'marked' => 1
        ],
        'protocol' => [
            'type'  => 'radio',
            'label' => 'Protocol',
            'value' => [
                '0' => 'FTP',
                '1' => 'FTPS'
            ],
            'marked' => 0
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'label' => '&nbsp;',
            'value' => 'Update',
            'attributes' => [
                'class'  => 'update-btn'
            ]
        ],
        'base_path' => [
            'type'  => 'hidden',
            'value' => BASE_PATH
        ],
        'app_path' => [
            'type'  => 'hidden',
            'value' => APP_PATH
        ],
        'content_path' => [
            'type'  => 'hidden',
            'value' => CONTENT_PATH
        ],
        'resource' => [
            'type'  => 'hidden',
            'value' => ''
        ]
    ]
];

