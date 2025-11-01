<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\ModuleBuilder;
use Marwa\Module\ModuleRegistry;
use Marwa\Module\ModuleRepository;
use Marwa\Module\ModulesServiceProvider;
use Marwa\Module\ModuleCacheAdapter;
use Marwa\Module\Tests\Container\FakeContainer;
use PHPUnit\Framework\TestCase;

class ModulesServiceProviderTest extends TestCase
{
      protected function setUp(): void
      {
            ModuleCacheAdapter::clear();
      }

      public function test_global_provider_registers_core_services(): void
      {
            $container = new FakeContainer();
            $modulesPath = __DIR__ . '/Fixtures/modules';

            $provider = new ModulesServiceProvider($modulesPath);
            $provider->register($container);

            $this->assertTrue($container->has(ModuleRepository::class));
            $this->assertTrue($container->has(ModuleRegistry::class));
            $this->assertTrue($container->has(ModuleBuilder::class));
      }

      public function test_global_provider_adds_module_service_providers(): void
      {
            $container = new FakeContainer();
            $modulesPath = __DIR__ . '/Fixtures/modules';

            $provider = new ModulesServiceProvider($modulesPath);
            $provider->register($container);

            // boot providers to trigger their boot()
            $container->bootProviders();

            // our test module providers should have registered flags
            $this->assertTrue($container->get('test.user.provider.registered'));
            //$this->assertTrue($container->get('test.user.provider.booted'));

            //$this->assertTrue($container->get('test.billing.provider.registered'));
            //$this->assertTrue($container->get('test.billing.provider.booted'));
      }
}
