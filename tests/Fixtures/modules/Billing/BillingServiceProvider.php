<?php

declare(strict_types=1);

namespace Marwa\Module\Tests\Fixtures\Billing;

use Marwa\Module\Contracts\ModuleServiceProviderInterface;
use Marwa\Module\Tests\Support\MutableContainerInterface;

class BillingServiceProvider implements ModuleServiceProviderInterface
{
    private MutableContainerInterface $container;

    public function setContainer(MutableContainerInterface $container): void
    {
        $this->container = $container;
    }

    public function register($app): void
    {
        $this->container->set('test.billing.provider.registered', true);
    }

    public function boot($app): void
    {
        $this->container->set('test.billing.provider.booted', true);
    }
}
