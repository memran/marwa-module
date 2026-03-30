<?php

declare(strict_types=1);

namespace Marwa\Module;

use Marwa\Module\Support\ModulePath;

/**
 * Cached access to modules discovered by the repository.
 * Also helps to find module by path.
 */
class ModuleRegistry
{
    /** @var array<string,Module> */
    private array $modules = [];

    public function __construct(
        private ModuleRepository $repository,
        private bool $forceRefresh = false
    ) {
        $this->reload();
    }

    public function reload(): void
    {
        $this->modules = $this->repository->all($this->forceRefresh);
    }

    /**
     * @return array<string, Module>
     */
    public function all(): array
    {
        return $this->modules;
    }

    public function has(string $slug): bool
    {
        return isset($this->modules[$slug]);
    }

    public function get(string $slug): ?Module
    {
        return $this->modules[$slug] ?? null;
    }

    /**
     * Resolve module by any path inside it.
     */
    public function findByPath(string $path): ?Module
    {
        $path = ModulePath::normalize(realpath($path) ?: $path);

        foreach ($this->modules as $module) {
            $base = ModulePath::normalize(realpath($module->basePath()) ?: $module->basePath());
            if (ModulePath::isWithinBasePath($path, $base)) {
                return $module;
            }
        }

        return null;
    }
}
