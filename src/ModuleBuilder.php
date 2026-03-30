<?php

declare(strict_types=1);

namespace Marwa\Module;

use Marwa\Module\Contracts\ModuleRegistryInterface;
use Marwa\Module\Exception\ModuleNotFoundException;

/**
 * Thin layer over container + registry.
 * This is what modules will actually call.
 */
class ModuleBuilder
{
    public function __construct(
        private ModuleRegistryInterface $registry
    ) {}

    /**
     * Get a handle by module slug.
     */
    public function current(string $slug): ModuleHandle
    {
        $module = $this->registry->get($slug);
        if ($module === null) {
            throw new ModuleNotFoundException("Module [$slug] not found.");
        }

        return new ModuleHandle($module);
    }

    /**
     * Resolve handle by path inside the module.
     */
    public function for(string $path): ModuleHandle
    {
        $module = $this->registry->findByPath($path);
        if ($module === null) {
            throw new ModuleNotFoundException("No module registered for path [$path].");
        }

        return new ModuleHandle($module);
    }

    public function has(string $slug): bool
    {
        return $this->registry->has($slug);
    }

    /**
     * @return array<string, Module>
     */
    public function all(): array
    {
        return $this->registry->all();
    }
}
