<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function test_it_is_json_serializable(): void
    {
        $module = new Module('blog', '/modules/Blog', [
              'name'    => 'Blog Module',
              'version' => '1.2.0',
              'menu'    => 'Blog',
        ]);

        $encoded = json_encode($module);
        $this->assertNotFalse($encoded);

        $decoded = json_decode($encoded, true);
        $this->assertSame('blog', $decoded['slug']);
        $this->assertSame('Blog Module', $decoded['name']);
        $this->assertSame('1.2.0', $decoded['version']);
        $this->assertSame('Blog', $decoded['manifest']['menu']);
    }

    public function test_debug_info_returns_key_fields(): void
    {
        $module = new Module('blog', '/modules/Blog', [
              'name'    => 'Blog Module',
              'version' => '1.2.0',
        ]);

        $info = $module->__debugInfo();

        $this->assertSame('blog', $info['slug']);
        $this->assertSame('Blog Module', $info['name']);
        $this->assertSame('1.2.0', $info['version']);
    }

    public function test_it_returns_custom_manifest_key_via_get(): void
    {
        $module = new Module('blog', '/modules/Blog', [
              'menu' => 'Blog',
              'permissions' => ['manage_blog'],
              'config' => ['editor' => 'markdown'],
        ]);

        $this->assertSame('Blog', $module->get('menu'));
        $this->assertSame(['manage_blog'], $module->get('permissions'));
        $this->assertSame(['editor' => 'markdown'], $module->get('config'));
        $this->assertNull($module->get('nonexistent'));
        $this->assertSame('fallback', $module->get('missing', 'fallback'));
    }

    public function test_it_filters_invalid_provider_entries(): void
    {
        $module = new Module('user', '/modules/User', [
              'providers' => [
                    ' App\\Providers\\UserProvider ',
                    '',
                    null,
                    'App\\Providers\\UserProvider',
              ],
        ]);

        $this->assertSame([
              'App\\Providers\\UserProvider',
        ], $module->providers());
    }

    public function test_it_rejects_paths_that_escape_the_module_directory(): void
    {
        $module = new Module('user', '/modules/User', [
              'paths' => [
                    'views' => '../shared/views',
              ],
              'routes' => [
                    'http' => '/etc/passwd',
              ],
              'migrations' => [
                    '../database/migrations/001.php',
                    'database/migrations/002.php',
              ],
        ]);

        $this->assertNull($module->path('views'));
        $this->assertNull($module->routeFile());
        $this->assertSame([
              '/modules/User/database/migrations/002.php',
        ], $module->migrations());
    }
}
