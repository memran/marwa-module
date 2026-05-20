<?php

declare(strict_types=1);

return array(
  'generated_at' => 1779275102,
  'modules' =>
  array(
    'auth' =>
    array(
      'slug' => 'auth',
      'basePath' => '/Users/memran/projects/php-projects/marwa-module/examples/modules/auth',
      'manifest' =>
      array(
        'name' => 'Auth Module',
        'slug' => 'auth',
        'version' => '1.0.0',
        'menu' => 'Authentication',
        'permissions' =>
        array(
          0 => 'auth.manage',
          1 => 'auth.login',
        ),
        'providers' =>
        array(
          0 => 'Marwa\\Module\\Examples\\Modules\\Auth\\AuthServiceProvider',
        ),
        'paths' =>
        array(
          'views' => 'resources/views',
        ),
        'routes' =>
        array(
          'http' => 'routes/http.php',
        ),
        'migrations' =>
        array(
          0 => 'database/migrations/2026_01_01_000000_create_auth_tables.php',
        ),
      ),
    ),
    'billing' =>
    array(
      'slug' => 'billing',
      'basePath' => '/Users/memran/projects/php-projects/marwa-module/examples/modules/billing',
      'manifest' =>
      array(
        'name' => 'Billing Module',
        'slug' => 'billing',
        'version' => '2.3.0',
        'providers' =>
        array(
          0 => 'Marwa\\Module\\Examples\\Modules\\Billing\\BillingServiceProvider',
        ),
        'paths' =>
        array(
          'views' => 'resources/views',
        ),
        'routes' =>
        array(
          'http' => 'routes/http.php',
        ),
        'migrations' =>
        array(
          0 => 'database/migrations/2026_01_01_000000_create_invoices_table.php',
        ),
      ),
    ),
  ),
);
