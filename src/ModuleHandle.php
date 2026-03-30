<?php

// src/ModuleHandle.php
declare(strict_types=1);

namespace Marwa\Module;

final class ModuleHandle
{
    public function __construct(
        private Module $module,
    ) {}

    public function slug(): string
    {
        return $this->module->slug();
    }
    public function name(): string
    {
        return $this->module->name();
    }
    public function basePath(): string
    {
        return $this->module->basePath();
    }
    public function path(string $key): ?string
    {
        return $this->module->path($key);
    }
    public function routes(string $channel = 'http'): ?string
    {
        return $this->module->routeFile($channel);
    }
    /** @return string[] */ public function migrations(): array
    {
        return $this->module->migrations();
    }
    /**
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        return $this->module->manifest();
    }
}
