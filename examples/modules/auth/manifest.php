<?php

declare(strict_types=1);

return [
      'name'      => 'Auth Module',
      'slug'      => 'auth',
      'version'   => '1.0.0',
      'providers' => [
            'App\Modules\Auth\UserServiceProvider::class',
      ],
      'paths'     => [
            'views' => 'src/Views',
      ],
      'routes'    => [
            'http' => 'routes/http.php',
      ],
];
