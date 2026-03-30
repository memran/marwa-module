<?php

declare(strict_types=1);

namespace Marwa\Module;

use Marwa\Module\Contracts\ModuleServiceProviderInterface;
use Marwa\Module\Exception\ModuleConfigurationException;

final class ModulesServiceProvider implements ModuleServiceProviderInterface
{
    /**
     * @param string|array<int, string> $modulesPath
     */
    public function __construct(
        private string|array $modulesPath,
        private ?string $cacheFile = null,
        private bool $forceRefresh = false
    ) {}

    public function register($app): void
    {
        $repository = new ModuleRepository($this->modulesPath, $this->cacheFile);
        $registry = new ModuleRegistry($repository, $this->forceRefresh);
        $builder = new ModuleBuilder($registry);
        $registeredProviders = [];

        $this->store($app, ModuleRepository::class, $repository);
        $this->store($app, ModuleRegistry::class, $registry);
        $this->store($app, ModuleBuilder::class, $builder);

        foreach ($registry->all() as $module) {
            foreach ($module->providers() as $provider) {
                if (!is_string($provider) || $provider === '') {
                    continue;
                }

                $this->assertValidProvider($provider);
                if (isset($registeredProviders[$provider])) {
                    continue;
                }

                $registeredProviders[$provider] = true;
                $this->addServiceProvider($app, $provider);
            }
        }
    }

    public function boot($app): void {}

    private function store(object $app, string $id, mixed $value): void
    {
        if (method_exists($app, 'add')) {
            $app->add($id, $value);
            return;
        }

        if (method_exists($app, 'set')) {
            $app->set($id, $value);
            return;
        }

        throw new ModuleConfigurationException('Container must provide add() or set() to register module services.');
    }

    private function addServiceProvider(object $app, string $provider): void
    {
        if (!method_exists($app, 'addServiceProvider')) {
            throw new ModuleConfigurationException(
                'Container must provide addServiceProvider() to register module providers.'
            );
        }

        $app->addServiceProvider($provider);
    }

    private function assertValidProvider(string $provider): void
    {
        if (!class_exists($provider)) {
            throw new ModuleConfigurationException("Module service provider [$provider] does not exist.");
        }

        if (!is_subclass_of($provider, ModuleServiceProviderInterface::class)) {
            throw new ModuleConfigurationException(
                "Module service provider [$provider] must implement " . ModuleServiceProviderInterface::class . '.'
            );
        }
    }
}
