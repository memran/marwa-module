<?php

declare(strict_types=1);

namespace Marwa\Module\Tests\Fixtures\User;

use Marwa\Module\Contracts\ModuleServiceProviderInterface;
use Marwa\Module\Tests\Support\MutableContainerInterface;

class UserServiceProvider implements ModuleServiceProviderInterface
{
    private MutableContainerInterface $container;

    public function setContainer(MutableContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function register($app): void
    {
        // just mark that we were called
        if (!isset($this->container)) {
            throw new \RuntimeException("Container not set in UserServiceProvider");
        }
        $this->container->add('test.user.provider.registered', true);
    }

    public function boot($app): void
    {
        $this->container->add('test.user.provider.booted', true);
    }
}
