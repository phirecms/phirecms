<?php
/**
 * Phire CMS install config form configuration
 */
return [
    [
        'config' => [
            'type'       => 'textarea',
            'required'   => true,
            'value'      => 'Hello!',
            'attributes' => [
                'class'   => 'form-control',
                'rows'    => 25,
                'cols'    => 120,
                'onfocus' => 'this.select();'
            ]
        ]
    ],
    [
        'submit' => [
            'type'  => 'submit',
            'value' => 'Continue',
            'attributes' => [
                'class'  => 'btn btn-info text-uppercase',
                'style'  => 'float: right;'
            ]
        ]
    ]
];

