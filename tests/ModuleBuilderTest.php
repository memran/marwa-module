<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\ModuleBuilder;
use Marwa\Module\ModuleRegistry;
use Marwa\Module\ModuleRepository;
use Marwa\Module\ModuleCacheAdapter;
use Marwa\Module\Tests\Container\FakeContainer;
use PHPUnit\Framework\TestCase;

class ModuleBuilderTest extends TestCase
{
      protected function setUp(): void
      {
            ModuleCacheAdapter::clear();
      }

      public function test_builder_returns_handle_for_module(): void
      {
            $repo = new ModuleRepository(__DIR__ . '/Fixtures/modules');
            $registry = new ModuleRegistry($repo);
            $container = new FakeContainer();

            $builder = new ModuleBuilder($registry, $container);

            $handle = $builder->current('user');

            $this->assertSame('user', $handle->slug());
            $this->assertNotEmpty($handle->basePath());
            $this->assertIsArray($handle->manifest());
      }
}
