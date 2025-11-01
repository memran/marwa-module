<?php

declare(strict_types=1);

namespace Marwa\Module\Tests\Fixtures\User;

use Marwa\Module\Contracts\ModuleServiceProviderInterface;
use Psr\Container\ContainerInterface;

class UserServiceProvider implements ModuleServiceProviderInterface
{
      private ContainerInterface $container;

      public function setContainer(ContainerInterface $container): void
      {
            $this->container = $container;
      }

      public function register($app): void
      {
            // just mark that we were called
            $this->container->add('test.user.provider.registered', true);
      }

      public function boot($app): void
      {
            $this->container->add('test.user.provider.booted', true);
      }
}
