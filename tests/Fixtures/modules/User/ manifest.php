<?php

return [
      'name'      => 'User Module',
      'slug'      => 'user',
      'version'   => '1.0.0',
      'providers' => [
            \Marwa\Module\Tests\Fixtures\User\UserServiceProvider::class,
      ],
      'paths'     => [
            'views' => 'src/Views',
      ],
      'routes'    => [
            'http' => 'routes/http.php',
      ],
];
