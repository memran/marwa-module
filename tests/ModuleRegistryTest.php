<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\ModuleRegistry;
use Marwa\Module\ModuleRepository;
use PHPUnit\Framework\TestCase;

class ModuleRegistryTest extends TestCase
{
    private string $tempRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempRoot = sys_get_temp_dir() . '/marwa-module-registry-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempRoot);
        parent::tearDown();
    }

    public function test_registry_can_get_module_by_slug(): void
    {
        $repo = new ModuleRepository(__DIR__ . '/Fixtures/modules');
        $registry = new ModuleRegistry($repo);

        $this->assertTrue($registry->has('user'));

        $user = $registry->get('user');
        $this->assertNotNull($user);
        $this->assertSame('user', $user->slug());
    }

    public function test_registry_can_find_by_path(): void
    {
        $repo = new ModuleRepository(__DIR__ . '/Fixtures/modules');
        $registry = new ModuleRegistry($repo);

        $userModulePath = realpath(__DIR__ . '/Fixtures/modules/User/src') ?: __DIR__ . '/Fixtures/modules/User/src';

        $found = $registry->findByPath($userModulePath);
        $this->assertNotNull($found);
        $this->assertSame('user', $found->slug());
    }

    public function test_registry_does_not_match_paths_outside_module_boundaries(): void
    {
        $modulesPath = $this->tempRoot . '/modules';
        $realModulePath = $modulesPath . '/User';
        $lookalikePath = $this->tempRoot . '/outside/UserBackup/src';

        mkdir($realModulePath, 0777, true);
        mkdir($lookalikePath, 0777, true);

        file_put_contents($realModulePath . '/manifest.php', <<<'PHP'
<?php
return [
    'slug' => 'user',
];
PHP);

        $repo = new ModuleRepository($modulesPath);
        $registry = new ModuleRegistry($repo);

        $this->assertNull($registry->findByPath($lookalikePath));
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                $this->removeDirectory($itemPath);
                continue;
            }

            unlink($itemPath);
        }

        rmdir($path);
    }
}
