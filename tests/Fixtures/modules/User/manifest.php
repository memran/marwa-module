<?php

declare(strict_types=1);

return [
      'name'       => 'User Module',
      'slug'       => 'user',
      'version'    => '1.0.0',
      'providers'  => [
            \Marwa\Module\Tests\Fixtures\User\UserServiceProvider::class,
      ],
      'paths'      => [
            'views' => 'src/Views',
      ],
      'routes'     => [
            'http' => 'routes/http.php',
      ],
      'migrations' => [
            'database/migrations/2026_01_01_000000_create_users_table.php',
      ],
];
