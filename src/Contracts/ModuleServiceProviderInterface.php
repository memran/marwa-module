<?php

declare(strict_types=1);

namespace Marwa\Module\Contracts;

/**
 * Every module MUST have a service provider that knows
 * how to register/bind its own routes, views, events, etc.
 */
interface ModuleServiceProviderInterface
{
    /**
     * Register services, bindings, listeners, etc.
     *
     * @param mixed $app PSR-11 container or framework app
     */
    public function register($app): void;

    /**
     * Boot logic that depends on other services being registered.
     *
     * @param mixed $app
     */
    public function boot($app): void;
}
