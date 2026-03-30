<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\Module;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
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
