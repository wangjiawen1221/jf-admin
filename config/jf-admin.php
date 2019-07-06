<?php

return [
    'title' => 'jf-admin',
    'caption' => 'JFA+',

    'directory' => app_path('JFAdmin'),

    'pagination' => [
        'num' => 20,
    ],

    'super_role' => 'Super Admin',

    'auth' => [
        'guards' => [
            'admin_user' => [
                'driver' => 'session',
                'provider' => 'admin_users',
            ],
        ],

        'providers' => [
            'admin_users' => [
                'driver' => 'eloquent',
                'model' => Imzhi\JFAdmin\Models\AdminUser::class,
            ],
        ],
    ],

    'route' => [
        'prefix' => 'jf-admin',
        'namespace' => 'App\\JFAdmin\\Controllers',
        'as' => 'jf-admin::',
        'middleware' => ['web', 'jf-admin'],
        'domain' => env('JFA_ROUTE_DOMAIN'),
    ],
];
