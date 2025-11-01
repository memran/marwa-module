<?php

declare(strict_types=1);

namespace Marwa\Module\Tests\Container;

use Psr\Container\ContainerInterface;
use Marwa\Module\Contracts\ModuleServiceProviderInterface;

/**
 * Very small container for testing.
 * - supports PSR-11 get/has
 * - supports set()
 * - supports addServiceProvider() which stores providers and runs register()
 */
class FakeContainer implements ContainerInterface
{
      /**
       * @var array<string,mixed>
       */
      private array $entries = [];

      /**
       * @var array<int,ModuleServiceProviderInterface>
       */
      private array $providers = [];

      public function add(string $id, mixed $value): void
      {
            $this->entries[$id] = $value;
      }

      public function get(string $id): mixed
      {
            if (!$this->has($id)) {
                  throw new \RuntimeException("Entry [$id] not found in container.");
            }
            return $this->entries[$id];
      }

      public function has(string $id): bool
      {
            return array_key_exists($id, $this->entries);
      }

      /**
       * Simulate your $container->addServiceProvider(...) API.
       */
      public function addServiceProvider(string $providerClass): void
      {
            /** @var ModuleServiceProviderInterface $provider */
            $provider = new $providerClass();

            // give the container to the provider (container-aware style)
            if (method_exists($provider, 'setContainer')) {
                  $provider->setContainer($this);
            }

            $provider->register($this);

            $this->providers[] = $provider;
      }

      /**
       * Call boot() on all registered providers (if any)
       */
      public function bootProviders(): void
      {
            foreach ($this->providers as $provider) {
                  $provider->boot($this);
            }
      }
}
