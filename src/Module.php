<?php

declare(strict_types=1);

namespace Marwa\Module;

use Marwa\Module\Support\ModulePath;
use Marwa\Support\Arr;

final class Module
{
    /**
     * @param array<string, mixed> $manifest
     */
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
        return (string) Arr::get($this->manifest, 'name', $this->slug);
    }
    public function version(): ?string
    {
        $version = Arr::get($this->manifest, 'version');
        return is_string($version) ? $version : null;
    }
    /**
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        return $this->manifest;
    }

    /** @return string[] */
    public function providers(): array
    {
        $providers = Arr::get($this->manifest, 'providers', []);
        if (!is_array($providers)) {
            return [];
        }

        $normalized = [];
        foreach ($providers as $provider) {
            if (!is_string($provider)) {
                continue;
            }

            $provider = trim($provider);
            if ($provider === '') {
                continue;
            }

            $normalized[] = $provider;
        }

        return array_values(array_unique($normalized));
    }

    public function path(string $key): ?string
    {
        $rel = Arr::get($this->manifest, "paths.{$key}");
        return is_string($rel)
              ? ModulePath::join($this->basePath, $rel)
              : null;
    }

    public function routeFile(string $channel = 'http'): ?string
    {
        $rel = Arr::get($this->manifest, "routes.{$channel}");
        return is_string($rel)
              ? ModulePath::join($this->basePath, $rel)
              : null;
    }

    /** @return string[] */
    public function migrations(): array
    {
        $files = Arr::get($this->manifest, 'migrations', []);
        if (!is_array($files)) {
            return [];
        }

        $out = [];
        foreach ($files as $rel) {
            if (!is_string($rel) || $rel === '') {
                continue;
            }

            $path = ModulePath::join($this->basePath, $rel);
            if ($path === null) {
                continue;
            }

            $out[] = $path;
        }
        return $out;
    }
}
