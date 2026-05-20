<?php

declare(strict_types=1);

use Marwa\Module\Examples\Modules\Auth\AuthServiceProvider;

return [
    'name' => 'Auth Module',
    'slug' => 'auth',
    'version' => '1.0.0',
    'menu' => 'Authentication',
    'permissions' => ['auth.manage', 'auth.login'],
    'providers' => [
        AuthServiceProvider::class,
    ],
    'paths' => [
        'views' => 'resources/views',
    ],
    'routes' => [
        'http' => 'routes/http.php',
    ],
    'migrations' => [
        'database/migrations/2026_01_01_000000_create_auth_tables.php',
    ],
];
