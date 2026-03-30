<?php

declare(strict_types=1);

namespace Marwa\Module\Examples\Modules\Billing;

use Marwa\Module\Contracts\ModuleServiceProviderInterface;
use Marwa\Module\Examples\ExampleApplication;

final class BillingServiceProvider implements ModuleServiceProviderInterface
{
    private ExampleApplication $container;

    public function setContainer(ExampleApplication $container): void
    {
        $this->container = $container;
    }

    public function register($app): void
    {
        $this->container->set('example.billing.registered', true);
    }

    public function boot($app): void
    {
        $this->container->set('example.billing.booted', true);
    }
}
