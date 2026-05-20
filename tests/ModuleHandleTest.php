<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\Module;
use Marwa\Module\ModuleHandle;
use PHPUnit\Framework\TestCase;

class ModuleHandleTest extends TestCase
{
    public function test_it_delegates_get_to_module(): void
    {
        $module = new Module('blog', '/modules/Blog', [
              'menu' => 'Blog Manager',
        ]);

        $handle = new ModuleHandle($module);

        $this->assertSame('Blog Manager', $handle->get('menu'));
        $this->assertNull($handle->get('nonexistent'));
        $this->assertSame('fallback', $handle->get('missing', 'fallback'));
    }

    public function test_it_delegates_providers_to_module(): void
    {
        $module = new Module('user', '/modules/User', [
              'providers' => [
                    'App\Providers\UserProvider',
              ],
        ]);

        $handle = new ModuleHandle($module);

        $this->assertSame(['App\Providers\UserProvider'], $handle->providers());
    }

    public function test_it_delegates_manifest_to_module(): void
    {
        $module = new Module('user', '/modules/User', [
              'menu' => 'Users',
        ]);

        $handle = new ModuleHandle($module);

        $this->assertSame(['menu' => 'Users'], $handle->manifest());
    }

    public function test_it_delegates_slug_name_basepath_path_routes_migrations(): void
    {
        $module = new Module('blog', '/modules/Blog', [
              'name' => 'Blog Module',
              'paths' => ['views' => 'resources/views'],
              'routes' => ['http' => 'routes/http.php'],
              'migrations' => ['001_create_posts.php'],
        ]);

        $handle = new ModuleHandle($module);

        $this->assertSame('blog', $handle->slug());
        $this->assertSame('Blog Module', $handle->name());
        $this->assertSame('/modules/Blog', $handle->basePath());
        $this->assertSame('/modules/Blog/resources/views', $handle->path('views'));
        $this->assertSame('/modules/Blog/routes/http.php', $handle->routes());
        $this->assertSame(['/modules/Blog/001_create_posts.php'], $handle->migrations());
    }
}
