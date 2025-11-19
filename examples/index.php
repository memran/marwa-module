<?php

declare(strict_types=1);

use Marwa\Module\ModuleBuilder;
use Marwa\Module\ModuleRegistry;
use Marwa\Module\ModuleRepository;

require_once '../vendor/autoload.php';

$modulesPath = __DIR__ . '/modules';
$cacheFile   = __DIR__ . '/storage/cache/modules.cache.php';
$repository = new ModuleRepository($modulesPath, cacheFile: $cacheFile);
$registry   = new ModuleRegistry($repository);
$builder    = new ModuleBuilder($registry);
// Later:
var_dump($builder->current('auth')->slug());

//$builder->current('billing')->migrations();
