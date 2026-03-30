<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\Exception\ModuleNotFoundException;
use Marwa\Module\ModuleBuilder;
use Marwa\Module\ModuleRegistry;
use Marwa\Module\ModuleRepository;
use PHPUnit\Framework\TestCase;

class ModuleBuilderTest extends TestCase
{
    public function test_builder_returns_handle_for_module(): void
    {
        $repo = new ModuleRepository(__DIR__ . '/Fixtures/modules');
        $registry = new ModuleRegistry($repo);
        $builder = new ModuleBuilder($registry);

        $handle = $builder->current('user');

        $this->assertSame('user', $handle->slug());
        $this->assertNotEmpty($handle->basePath());
    }

    public function test_builder_resolves_handle_from_path(): void
    {
        $repo = new ModuleRepository(__DIR__ . '/Fixtures/modules');
        $registry = new ModuleRegistry($repo);
        $builder = new ModuleBuilder($registry);

        $handle = $builder->for(__DIR__ . '/Fixtures/modules/User/routes/http.php');

        $this->assertSame('user', $handle->slug());
        $this->assertStringContainsString('routes/http.php', (string) $handle->routes());
    }

    public function test_builder_throws_for_unknown_module(): void
    {
        $repo = new ModuleRepository(__DIR__ . '/Fixtures/modules');
        $registry = new ModuleRegistry($repo);
        $builder = new ModuleBuilder($registry);

        $this->expectException(ModuleNotFoundException::class);

        $builder->current('missing');
    }
}
