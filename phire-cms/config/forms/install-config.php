<?php
/**
 * Phire\Form\InstallConfig configuration
 */
return [
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
                'onfocus' => 'this.select();',
                'class'   => 'form-control'
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

