<?php

namespace Phire\Form;

use Phire\Table;
use Pop\Form\Form;
use Pop\Validator;

class Role extends Form
{

    /**
     * Constructor
     *
     * Instantiate the form object
     *
     * @param  array  $permissions
     * @param  array  $fields
     * @param  string $action
     * @param  string $method
     * @return Role
     */
    public function __construct(array $permissions = [], $id = 0, array $fields = null, $action = null, $method = 'post')
    {
        $omitRoutes = [
            APP_URI . '/verify/:id/:hash',
            APP_URI . '/forgot[/]',
            APP_URI . '/unsubscribe[/]',
            APP_URI . '/logout[/]'
        ];

        $routes = array_keys(include __DIR__ . '/../../config/routes.php');
        $routeValues = ['----' => '----'];
        foreach ($routes as $route) {
            if (!in_array($route, $omitRoutes)) {
                $routeValues[$route] = str_replace('[/]', '', $route);
            }
        }

        $parentRoles = ['----' => '----'];

        $roles = Table\Roles::findAll();
        if ($roles->count() > 0) {
            foreach ($roles->rows() as $role) {
                if ($role['id'] != $id) {
                    $parentRoles[$role['id']] = $role['name'];
                }
            }
        }

        $fields = [
            'parent_id' => [
                'type'       => 'select',
                'label'      => 'Parent',
                'value'      => $parentRoles
            ],
            'name' => [
                'type'       => 'text',
                'label'      => 'Name',
                'required'   => 'true',
                'attributes' => [
                    'onkeyup' => 'phire.changeTitle(this.value);'
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
            'permission_new_1' => [
                'type'       => 'select',
                'label'      => '<a href="#" onclick="phire.addPermissions(); return false">[+]</a> Permissions',
                'value'      => $routeValues,
                'attributes' => [
                    'style' => 'display: block;'
                ]
            ],
            'allow_new_1' => [
                'type'     => 'select',
                'value'    => [
                    '----' => '----',
                    '0'    => 'deny',
                    '1'    => 'allow'
                ],
                'attributes' => [
                    'style' => 'display: block;'
                ]
            ]
        ];

        if (count($permissions) > 0) {
            $i = 1;
            foreach ($permissions as $route => $permission) {
                $fields['permission_cur_' . $i] = [
                    'type'       => 'select',
                    'label'      => '&nbsp;',
                    'value'      => $routeValues,
                    'attributes' => [
                        'style' => 'display: block;'
                    ],
                    'marked' => $route
                ];
                $fields['allow_cur_' . $i] = [
                    'type'     => 'select',
                    'value'    => [
                        '----' => '----',
                        '0'    => 'deny',
                        '1'    => 'allow'
                    ],
                    'attributes' => [
                        'style' => 'display: block;'
                    ],
                    'marked' => (int)$permission
                ];
                $fields['rm_permissions_' . $i] = [
                    'type' => 'checkbox',
                    'value' => [
                        $route => '&nbsp;'
                    ]
                ];
                $i++;
            }
        }

        $fields['submit'] = [
            'type'  => 'submit',
            'label' => '&nbsp;',
            'value' => 'Save'
        ];
        $fields['id'] = [
            'type'  => 'hidden',
            'value' => '0'
        ];

        parent::__construct($fields, $action, $method);

        $this->setAttribute('id', 'role-form');
        $this->setIndent('    ');
    }

}