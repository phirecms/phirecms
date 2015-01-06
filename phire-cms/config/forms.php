<?php

return [
    'Phire\Form\Forgot' => [
        [
            'email' => [
                'type'       => 'email',
                'label'      => 'Forgot Your Password?',
                'required'   => 'true',
                'validators' => new \Pop\Validator\Email(),
                'attributes' => [
                    'placeholder' => 'Please enter your email'
                ]
            ]
        ],
        [
            'submit' => [
                'type'  => 'submit',
                'value' => 'Submit',
                'attributes' => [
                    'class'  => 'save-btn'
                ]
            ]
        ]
    ],
    'Phire\Form\Install' => [
        [
            'db_adapter'   => [
                'type'     => 'select',
                'label'    => 'DB Adapter',
                'value'    => null
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
    ],
    'Phire\Form\InstallConfig' => [
        [
            'config' => [
                'type'       => 'textarea',
                'label'      => 'Configuration',
                'required'   => true,
                'value'      => null,
                'attributes' => [
                    'rows'    => 50,
                    'cols'    => 120,
                    'style'   => 'width: 99%; height: 420px; display: block; margin: 0 auto;',
                    'onfocus' => 'this.select();'
                ]
            ]
        ],
        [
            'submit' => [
                'type'  => 'submit',
                'value' => 'Continue',
                'attributes' => [
                    'class'  => 'save-btn',
                    'style'  => 'float: right; margin: 0 3px 0 0;'
                ]
            ]
        ]
    ],
    'Phire\Form\Login' => [
        [
            'username' => [
                'type'       => 'text',
                'required'   => 'true',
                'validators' => new \Pop\Validator\NotEmpty(),
                'attributes' => [
                    'placeholder' => 'Username'
                ]
            ],
            'password' => [
                'type'       => 'password',
                'required'   => 'true',
                'validators' => new \Pop\Validator\NotEmpty(),
                'attributes' => [
                    'placeholder' => 'Password'
                ]
            ]
        ],
        [
            'submit' => [
                'type'  => 'submit',
                'value' => 'Login',
                'attributes' => [
                    'class'  => 'save-btn'
                ]
            ]
        ]
    ],
    'Phire\Form\Profile' => [
        [
            'username' => [
                'type'     => 'text',
                'label'    => 'Username',
                'required' => true
            ],
            'email1' => [
                'type'       => 'email',
                'label'      => 'Email',
                'required'   => true,
                'validators' => new \Pop\Validator\Email()
            ],
            'email2' => [
                'type'      => 'email',
                'label'     => 'Re-Type Email'
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
    ],
    'Phire\Form\Register' => [
        [
            'username' => [
                'type'     => 'text',
                'label'    => 'Username',
                'required' => true
            ],
            'email1' => [
                'type'       => 'email',
                'label'      => 'Email',
                'required'   => true,
                'validators' => new \Pop\Validator\Email()
            ],
            'email2' => [
                'type'      => 'email',
                'required'  => true,
                'label'     => 'Re-Type Email'
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
    ],
    'Phire\Form\Unsubscribe' => [
        [
            'email' => [
                'type'       => 'email',
                'label'      => 'Unsubscribe',
                'required'   => 'true',
                'validators' => new \Pop\Validator\Email(),
                'attributes' => [
                    'placeholder' => 'Please enter your email'
                ]
            ]
        ],
        [
            'submit' => [
                'type'  => 'submit',
                'value' => 'Unsubscribe',
                'attributes' => [
                    'class'  => 'save-btn'
                ]
            ]
        ]
    ],
    'Phire\Form\User' => [
        [
            'submit' => [
                'type'       => 'submit',
                'value'      => 'Save',
                'attributes' => [
                    'class'  => 'save-btn wide'
                ]
            ],
            'role_id'  => null,
            'verified' => [
                'type'      => 'radio',
                'label'     => 'Verified',
                'value' => [
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
            'username' => [
                'type'     => 'text',
                'label'    => 'Username',
                'required' => true,
                'attributes' => [
                    'size'    => 40
                ]
            ],
            'email1' => [
                'type'       => 'email',
                'label'      => 'Email',
                'required'   => true,
                'validators' => new \Pop\Validator\Email(),
                'attributes' => [
                    'size'    => 40
                ]
            ],
            'email2' => [
                'type'      => 'email',
                'label'     => 'Re-Type Email',
                'attributes' => [
                    'size'    => 40
                ]
            ],
            'password1' => [
                'type'       => 'password',
                'label'      => 'Password',
                'validators' => new \Pop\Validator\LengthGte(6),
                'attributes' => [
                    'size'    => 40
                ]
            ],
            'password2' => [
                'type'      => 'password',
                'label'     => 'Re-Type Password',
                'attributes' => [
                    'size'    => 40
                ]
            ]
        ]
    ],
    'Phire\Form\UserRole' => [
        [
            'submit' => [
                'type'       => 'submit',
                'value'      => 'Save',
                'attributes' => [
                    'class'  => 'save-btn wide'
                ]
            ],
            'parent_id' => [
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
                    'size'    => 55,
                    'onkeyup' => 'phire.changeTitle(this.value);'
                ]
            ]
        ]
    ]
];

