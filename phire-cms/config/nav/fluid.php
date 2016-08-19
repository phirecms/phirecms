<?php
/**
 * Pop Web Bootstrap Application Framework side nav configuration
 */
return [
    'overview' => [
        'name' => 'Overview',
        'href' => '#',
        'children' => [
            'test1' => [
                'name' => '<span>Link with a Really Long Title</span>',
                'href' => '#',
            ],
            'test2' => [
                'name' => 'Short Title',
                'href' => '#',
            ]
        ]
    ],
    'reports' => [
        'name' => 'Reports',
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
    'analytics' => [
        'name' => 'Analytics',
        'href' => '#'
    ],
    'export' => [
        'name' => 'Export',
        'href' => '#'
    ]
];