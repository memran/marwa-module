<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\Exception\InvalidManifestException;
use Marwa\Module\Module;
use Marwa\Module\ModuleCache;
use Marwa\Module\ModuleRepository;
use PHPUnit\Framework\TestCase;

class ModuleRepositoryTest extends TestCase
{
    private string $fixturesDir;
    private string $cacheFile;
    private string $tempRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturesDir = __DIR__ . '/Fixtures/modules';
        $this->cacheFile = sys_get_temp_dir() . '/marwa-module-test-cache.php';
        $this->tempRoot = sys_get_temp_dir() . '/marwa-module-repository-' . uniqid('', true);
        ModuleCache::clear($this->cacheFile);
    }

    protected function tearDown(): void
    {
        ModuleCache::clear($this->cacheFile);
        $this->removeDirectory($this->tempRoot);
        parent::tearDown();
    }

    public function test_it_scans_modules_directory_once(): void
    {
        $repo = new ModuleRepository($this->fixturesDir);

        $modules = $repo->all();

        $this->assertCount(2, $modules);
        $this->assertArrayHasKey('user', $modules);
        $this->assertArrayHasKey('billing', $modules);

        $this->assertInstanceOf(Module::class, $modules['user']);
        $this->assertSame('user', $modules['user']->slug());
    }

    public function test_it_can_hydrate_modules_from_cache_file(): void
    {
        $repo = new ModuleRepository($this->fixturesDir, $this->cacheFile);
        $first = $repo->all();
        $this->assertFileExists($this->cacheFile);

        ModuleCache::save($this->cacheFile, [
              'generated_at' => time(),
              'modules' => [
                    'cached-module' => [
                          'slug' => 'cached-module',
                          'basePath' => '/tmp/cached-module',
                          'manifest' => [
                                'name' => 'Cached Module',
                                'slug' => 'cached-module',
                                'providers' => [],
                                'paths' => [],
                                'routes' => [],
                                'migrations' => [],
                          ],
                    ],
              ],
        ]);

        $second = $repo->all();

        $this->assertCount(2, $first);
        $this->assertCount(1, $second);
        $this->assertArrayHasKey('cached-module', $second);
        $this->assertSame('cached-module', $second['cached-module']->slug());
    }

    public function test_it_falls_back_to_rescanning_when_cache_is_corrupted(): void
    {
        $repo = new ModuleRepository($this->fixturesDir, $this->cacheFile);

        ModuleCache::save($this->cacheFile, [
              'generated_at' => time(),
              'modules' => [
                    'broken' => 'invalid',
              ],
        ]);

        $modules = $repo->all();

        $this->assertCount(2, $modules);
        $this->assertArrayHasKey('user', $modules);
    }

    public function test_it_throws_for_invalid_json_manifest(): void
    {
        $modulePath = $this->tempRoot . '/BrokenModule';
        mkdir($modulePath, 0777, true);
        file_put_contents($modulePath . '/manifest.json', '{invalid-json');

        $repo = new ModuleRepository($this->tempRoot);

        $this->expectException(InvalidManifestException::class);

        $repo->all();
    }

    public function test_it_ignores_directories_without_a_manifest(): void
    {
        $modulePath = $this->tempRoot . '/NoManifestModule';
        mkdir($modulePath, 0777, true);

        $repo = new ModuleRepository($this->tempRoot);

        $this->assertSame([], $repo->all());
    }

    public function test_it_throws_when_both_manifest_formats_exist(): void
    {
        $modulePath = $this->tempRoot . '/ConflictingModule';
        mkdir($modulePath, 0777, true);
        file_put_contents($modulePath . '/manifest.php', <<<'PHP'
<?php
return [
    'slug' => 'conflicting-module',
];
PHP);
        file_put_contents($modulePath . '/manifest.json', '{"slug":"conflicting-module"}');

        $repo = new ModuleRepository($this->tempRoot);

        $this->expectException(InvalidManifestException::class);

        $repo->all();
    }

    public function test_it_throws_for_invalid_manifest_schema(): void
    {
        $modulePath = $this->tempRoot . '/SchemaBrokenModule';
        mkdir($modulePath, 0777, true);
        file_put_contents($modulePath . '/manifest.php', <<<'PHP'
<?php
return [
    'slug' => 'schema-broken',
    'providers' => 'not-an-array',
];
PHP);

        $repo = new ModuleRepository($this->tempRoot);

        $this->expectException(InvalidManifestException::class);

        $repo->all();
    }

    public function test_it_throws_for_duplicate_module_slugs(): void
    {
        $firstPath = $this->tempRoot . '/FirstModule';
        $secondPath = $this->tempRoot . '/SecondModule';
        mkdir($firstPath, 0777, true);
        mkdir($secondPath, 0777, true);

        file_put_contents($firstPath . '/manifest.php', <<<'PHP'
<?php
return [
    'slug' => 'duplicate-slug',
];
PHP);
        file_put_contents($secondPath . '/manifest.php', <<<'PHP'
<?php
return [
    'slug' => 'duplicate-slug',
];
PHP);

        $repo = new ModuleRepository($this->tempRoot);

        $this->expectException(InvalidManifestException::class);

        $repo->all();
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
