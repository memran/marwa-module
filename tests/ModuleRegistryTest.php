<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\ModuleRegistry;
use Marwa\Module\ModuleRepository;
use Marwa\Module\ModuleCacheAdapter;
use PHPUnit\Framework\TestCase;

class ModuleRegistryTest extends TestCase
{
      protected function setUp(): void
      {
            ModuleCacheAdapter::clear();
      }

      public function test_registry_can_get_module_by_slug(): void
      {
            $repo = new ModuleRepository(__DIR__ . '/Fixtures/modules');
            $registry = new ModuleRegistry($repo);

            $this->assertTrue($registry->has('user'));

            $user = $registry->get('user');
            $this->assertNotNull($user);
            $this->assertSame('user', $user->getSlug());
      }

      public function test_registry_can_find_by_path(): void
      {
            $repo = new ModuleRepository(__DIR__ . '/Fixtures/modules');
            $registry = new ModuleRegistry($repo);

            $userModulePath = realpath(__DIR__ . '/Fixtures/modules/User/src') ?: __DIR__ . '/Fixtures/modules/User/src';

            $found = $registry->findByPath($userModulePath);
            $this->assertNotNull($found);
            $this->assertSame('user', $found->getSlug());
      }
}
