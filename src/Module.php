<?php

declare(strict_types=1);

namespace Marwa\Module;

/**
 * Immutable representation of a single module.
 */
class Module
{
      public function __construct(
            private string $slug,
            private string $basePath,
            private array $manifest
      ) {}

      public function getSlug(): string
      {
            return $this->slug;
      }

      public function getBasePath(): string
      {
            return $this->basePath;
      }

      public function getName(): string
      {
            return $this->manifest['name'] ?? $this->slug;
      }
      /**
       * @return string|null
       */
      public function getVersion(): ?string
      {
            return $this->manifest['version'] ?? null;
      }

      /**
       * @return string[]
       */
      public function getProviders(): array
      {
            return $this->manifest['providers'] ?? [];
      }

      /**
       * @return array<string,string>
       */
      public function getPaths(): array
      {
            return $this->manifest['paths'] ?? [];
      }

      public function getPath(string $key): ?string
      {
            $paths = $this->getPaths();
            if (!isset($paths[$key])) {
                  return null;
            }

            return rtrim($this->basePath . DIRECTORY_SEPARATOR . $paths[$key], DIRECTORY_SEPARATOR);
      }

      /**
       * @param string $channel e.g. 'http', 'cli'
       */
      public function getRouteFile(string $channel): ?string
      {
            if (!isset($this->manifest['routes'][$channel])) {
                  return null;
            }

            return $this->basePath . DIRECTORY_SEPARATOR . ltrim($this->manifest['routes'][$channel], DIRECTORY_SEPARATOR);
      }

      /**
       * @return string[]
       */
      public function getMigrations(): array
      {
            $files = $this->manifest['migrations'] ?? [];
            $result = [];
            foreach ($files as $file) {
                  $result[] = $this->basePath . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);
            }
            return $result;
      }

      /**
       * @return array<string,array<string,string>>
       */
      public function getAutoload(): array
      {
            return $this->manifest['autoload'] ?? [];
      }

      public function getManifest(): array
      {
            return $this->manifest;
      }
}
