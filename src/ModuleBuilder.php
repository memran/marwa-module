<?php

declare(strict_types=1);

namespace Marwa\Module;

use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Thin layer over container + registry.
 * This is what modules will actually call.
 */
class ModuleBuilder
{
      public function __construct(
            private ModuleRegistry $registry,
            private ContainerInterface $container
      ) {}

      /**
       * Get a handle by module slug.
       */
      public function current(string $slug): ModuleHandle
      {
            $module = $this->registry->get($slug);
            if ($module === null) {
                  throw new RuntimeException("Module [$slug] not found.");
            }

            return new ModuleHandle($module, $this->container);
      }

      /**
       * Resolve handle by path inside the module.
       */
      public function for(string $path): ModuleHandle
      {
            $module = $this->registry->findByPath($path);
            if ($module === null) {
                  throw new RuntimeException("No module registered for path [$path].");
            }

            return new ModuleHandle($module, $this->container);
      }

      public function has(string $slug): bool
      {
            return $this->registry->has($slug);
      }

      public function all(): array
      {
            return $this->registry->all();
      }

      public function container(): ContainerInterface
      {
            return $this->container;
      }
}
