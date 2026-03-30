<?php

declare(strict_types=1);

namespace Marwa\Module\Tests\Support;

use Psr\Container\ContainerInterface;

interface MutableContainerInterface extends ContainerInterface
{
    public function add(string $id, mixed $value): void;

    public function set(string $id, mixed $value): void;
}
