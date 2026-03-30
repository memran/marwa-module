<?php

declare(strict_types=1);
return array(
  'generated_at' => 1763552239,
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
        'providers' =>
        array(
          0 => 'App\\Modules\\Auth\\UserServiceProvider',
        ),
        'paths' =>
        array(
          'views' => 'src/Views',
        ),
        'routes' =>
        array(
          'http' => 'routes/http.php',
        ),
      ),
    ),
  ),
);
