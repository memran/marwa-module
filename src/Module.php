<?php

declare(strict_types=1);

namespace Marwa\Module;

final class Module
{
      public function __construct(
            private string $slug,
            private string $basePath,
            private array  $manifest
      ) {}

      public function slug(): string
      {
            return $this->slug;
      }
      public function basePath(): string
      {
            return $this->basePath;
      }
      public function name(): string
      {
            return $this->manifest['name'] ?? $this->slug;
      }
      public function version(): ?string
      {
            return $this->manifest['version'] ?? null;
      }
      public function manifest(): array
      {
            return $this->manifest;
      }

      /** @return string[] */
      public function providers(): array
      {
            return $this->manifest['providers'] ?? [];
      }

      public function path(string $key): ?string
      {
            $rel = $this->manifest['paths'][$key] ?? null;
            return $rel ? $this->basePath . DIRECTORY_SEPARATOR . ltrim($rel, DIRECTORY_SEPARATOR) : null;
      }

      public function routeFile(string $channel = 'http'): ?string
      {
            $rel = $this->manifest['routes'][$channel] ?? null;
            return $rel ? $this->basePath . DIRECTORY_SEPARATOR . ltrim($rel, DIRECTORY_SEPARATOR) : null;
      }

      /** @return string[] */
      public function migrations(): array
      {
            $files = $this->manifest['migrations'] ?? [];
            $out = [];
            foreach ($files as $rel) {
                  $out[] = $this->basePath . DIRECTORY_SEPARATOR . ltrim($rel, DIRECTORY_SEPARATOR);
            }
            return $out;
      }
}
