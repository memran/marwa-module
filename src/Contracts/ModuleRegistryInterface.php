<?php

declare(strict_types=1);

namespace Marwa\Module\Contracts;

use Marwa\Module\Module;

interface ModuleRegistryInterface
{
    /**
     * @return array<string, Module>
     */
    public function all(): array;

    public function has(string $slug): bool;

    public function get(string $slug): ?Module;

    public function findByPath(string $path): ?Module;

    public function reload(): void;
}
