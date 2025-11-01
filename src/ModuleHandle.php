<?php

declare(strict_types=1);

namespace Marwa\Module;

use Psr\Container\ContainerInterface;

/**
 * Module-facing API that a module service provider will use.
 * Thin layer over Module + Container.
 */
class ModuleHandle
{
      public function __construct(
            private Module $module,
            private ContainerInterface $container
      ) {}

      public function slug(): string
      {
            return $this->module->getSlug();
      }

      public function name(): string
      {
            return $this->module->getName();
      }

      public function version(): ?string
      {
            return $this->module->getVersion();
      }

      public function basePath(): string
      {
            return $this->module->getBasePath();
      }

      public function path(string $key): ?string
      {
            return $this->module->getPath($key);
      }

      public function routes(string $channel = 'http'): ?string
      {
            return $this->module->getRouteFile($channel);
      }

      /**
       * @return string[]
       */
      public function migrations(): array
      {
            return $this->module->getMigrations();
      }

      public function manifest(): array
      {
            return $this->module->getManifest();
      }

      public function container(): ContainerInterface
      {
            return $this->container;
      }
}
