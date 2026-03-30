<?php

declare(strict_types=1);

namespace Marwa\Module\Tests;

use Marwa\Module\Contracts\ModuleRegistryInterface;
use Marwa\Module\Contracts\ModuleRepositoryInterface;
use Marwa\Module\Exception\ModuleConfigurationException;
use Marwa\Module\ModuleBuilder;
use Marwa\Module\ModuleRegistry;
use Marwa\Module\ModuleRepository;
use Marwa\Module\ModulesServiceProvider;
use Marwa\Module\Tests\Container\FakeContainer;
use PHPUnit\Framework\TestCase;

class ModulesServiceProviderTest extends TestCase
{
    private string $tempRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempRoot = sys_get_temp_dir() . '/marwa-module-provider-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempRoot);
        parent::tearDown();
    }

    public function test_global_provider_registers_core_services(): void
    {
        $container = new FakeContainer();
        $modulesPath = __DIR__ . '/Fixtures/modules';

        $provider = new ModulesServiceProvider($modulesPath);
        $provider->register($container);

        $this->assertTrue($container->has(ModuleRepositoryInterface::class));
        $this->assertTrue($container->has(ModuleRegistryInterface::class));
        $this->assertTrue($container->has(ModuleRepository::class));
        $this->assertTrue($container->has(ModuleRegistry::class));
        $this->assertTrue($container->has(ModuleBuilder::class));
    }

    public function test_global_provider_adds_module_service_providers(): void
    {
        $container = new FakeContainer();
        $modulesPath = __DIR__ . '/Fixtures/modules';

        $provider = new ModulesServiceProvider($modulesPath);
        $provider->register($container);
        $this->assertTrue($container->has(ModuleBuilder::class));
        $container->bootProviders();

        $this->assertTrue($container->get('test.user.provider.registered'));
        $this->assertTrue($container->get('test.user.provider.booted'));
        $this->assertTrue($container->get('test.billing.provider.registered'));
        $this->assertTrue($container->get('test.billing.provider.booted'));
    }

    public function test_global_provider_rejects_invalid_module_provider_classes(): void
    {
        $modulePath = $this->tempRoot . '/modules/Broken';
        mkdir($modulePath, 0777, true);

        file_put_contents($modulePath . '/manifest.php', <<<'PHP'
<?php
return [
    'slug' => 'broken',
    'providers' => [
        stdClass::class,
    ],
];
PHP);

        $container = new FakeContainer();
        $provider = new ModulesServiceProvider($this->tempRoot . '/modules');

        $this->expectException(ModuleConfigurationException::class);

        $provider->register($container);
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
