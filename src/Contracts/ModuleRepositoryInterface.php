<?php

declare(strict_types=1);

namespace Marwa\Module\Contracts;

use Marwa\Module\Module;

interface ModuleRepositoryInterface
{
    /**
     * @return array<string, Module>
     */
    public function all(bool $forceRefresh = false): array;
}
