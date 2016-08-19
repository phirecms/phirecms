<?php
/**
 * Pop Web Bootstrap Application Framework side nav configuration
 */
return [
    'content' => [
        'name' => 'Content',
        'href' => '#',
        'children' => [
            'test1' => [
                'name' => '<span>Link with a Really Long Title</span>',
                'href' => '#',
                'children' => [
                    'logins' => [
                        'name' => '<span>Another Really Long Link Title</span>',
                        'href' => '#',
                    ],
                    'foo' => [
                        'name' => 'Another Title',
                        'href' => '#',
                    ]
                ]
            ],
            'test2' => [
                'name' => 'Short Title',
                'href' => '#',
            ]
        ]
    ],
    'media' => [
        'name' => 'Media',
        'href' => '#',
        'children' => [
            'test3' => [
                'name' => 'Test',
                'href' => '#',
            ],
            'test4' => [
                'name' => 'Another Link',
                'href' => '#',
                'children' => [
                    'logins' => [
                        'name' => '<span>Another Really Long Link Title</span>',
                        'href' => '#',
                    ],
                    'foo' => [
                        'name' => 'Another Title',
                        'href' => '#',
                    ]
                ]
            ]
        ]
    ],
    'modules' => [
        'name' => 'Modules',
        'href' => '#'
    ],
    'config' => [
        'name' => 'Config',
        'href' => '#'
    ]
];