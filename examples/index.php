<?php

declare(strict_types=1);

use Marwa\Module\Contracts\ModuleRegistryInterface;
use Marwa\Module\Contracts\ModuleRepositoryInterface;
use Marwa\Module\Examples\ExampleApplication;
use Marwa\Module\ModuleBuilder as ConcreteModuleBuilder;
use Marwa\Module\ModuleCache;
use Marwa\Module\ModuleRegistry;
use Marwa\Module\ModuleRepository;
use Marwa\Module\ModulesServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/modules/auth/AuthServiceProvider.php';
require_once __DIR__ . '/modules/billing/BillingServiceProvider.php';

$modulesPath = __DIR__ . '/modules';
$cacheFile = __DIR__ . '/storage/cache/modules.cache.php';

if (is_file($cacheFile)) {
    ModuleCache::clear($cacheFile);
}

$repository = new ModuleRepository($modulesPath, $cacheFile);
$registry = new ModuleRegistry($repository);
$builder = new ConcreteModuleBuilder($registry);

$modules = [];
foreach ($builder->all() as $slug => $module) {
    $modules[] = [
        'slug' => $slug,
        'name' => $module->name(),
        'version' => $module->version(),
        'route' => $module->routeFile('http'),
        'views' => $module->path('views'),
        'providers' => $module->providers(),
        'migrations' => $module->migrations(),
    ];
}

$auth = $builder->current('auth');

$app = new ExampleApplication();
$globalProvider = new ModulesServiceProvider($modulesPath, $cacheFile);
$globalProvider->register($app);
$app->bootProviders();

/** @var ModuleRepositoryInterface $registeredRepository */
$registeredRepository = $app->get(ModuleRepository::class);
/** @var ModuleRegistryInterface $registeredRegistry */
$registeredRegistry = $app->get(ModuleRegistry::class);
/** @var ConcreteModuleBuilder $registeredBuilder */
$registeredBuilder = $app->get(ConcreteModuleBuilder::class);

$payload = [
    'modules' => $modules,
    'lookups' => [
        'current_auth_name' => $auth->name(),
        'resolved_by_path' => $builder->for(__DIR__ . '/modules/auth/routes/http.php')->slug(),
    ],
    'bootstrap' => [
        'repository_registered' => $registeredRepository instanceof ModuleRepository,
        'registry_registered' => $registeredRegistry instanceof ModuleRegistry,
        'builder_registered' => $registeredBuilder instanceof ConcreteModuleBuilder,
        'auth_provider_registered' => (bool) $app->get('example.auth.registered'),
        'auth_provider_booted' => (bool) $app->get('example.auth.booted'),
        'billing_provider_registered' => (bool) $app->get('example.billing.registered'),
        'billing_provider_booted' => (bool) $app->get('example.billing.booted'),
    ],
];

if (PHP_SAPI !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
}

echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
