<?php

return [
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
];

