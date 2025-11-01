<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\Module;
use Marwa\Module\ModuleCacheAdapter;
use Marwa\Module\ModuleRepository;
use PHPUnit\Framework\TestCase;

class ModuleRepositoryTest extends TestCase
{
      private string $fixturesDir;

      protected function setUp(): void
      {
            parent::setUp();
            $this->fixturesDir = __DIR__ . '/Fixtures/modules';
            // ensure clean cache for each test
            ModuleCacheAdapter::clear();
      }

      public function test_it_scans_modules_directory_once(): void
      {
            $repo = new ModuleRepository($this->fixturesDir);

            $modules = $repo->all();

            $this->assertCount(2, $modules);
            $this->assertArrayHasKey('User', $modules);
            $this->assertArrayHasKey('Billing', $modules);

            $this->assertInstanceOf(Module::class, $modules['User']);
            $this->assertSame('User', $modules['User']->getSlug());
      }

      public function test_second_call_uses_cache_not_rescan(): void
      {
            $repo = new ModuleRepository($this->fixturesDir);
            $first = $repo->all();

            // simulate that directory is gone now
            // (if repo rescans, this would fail)
            @rmdir($this->fixturesDir . '/__non_existent__');

            $second = $repo->all();

            $this->assertCount(count($first), $second);
            $this->assertArrayHasKey('User', $second);
      }
}
