<?php

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
];

