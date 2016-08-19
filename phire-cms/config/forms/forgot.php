<?php
/**
 * Pop Web Bootstrap Application Framework forgot form configuration
 */
return [
    [
        'email' => [
            'type'       => 'email',
            'required'   => 'true',
            'validators' => new \Pop\Validator\Email(),
            'attributes' => [
                'placeholder' => 'Please enter your email',
                'class'       => 'form-control'
            ]
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'value' => 'Submit',
            'attributes' => [
                'class'  => 'btn btn-lg btn-info btn-block text-uppercase'
            ]
        ]
    ]
];
