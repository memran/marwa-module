# marwa-module

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-blue?style=flat-square)
![Downloads](https://img.shields.io/packagist/dt/memran/marwa-module?color=brightgreen&style=flat-square)
![License](https://img.shields.io/badge/license-MIT-lightgrey?style=flat-square)

A framework-agnostic, PSR-11-friendly module discovery library for modular PHP applications.

`marwa-module` scans one or more module directories, loads module manifests, exposes a typed registry for lookup by slug or path, and can register module service providers into a container-aware application bootstrap.

## Features

- Filesystem-based module discovery
- PHP and JSON manifest support
- Typed `Module`, `ModuleHandle`, `ModuleRegistry`, and `ModuleBuilder` APIs
- Optional file-based cache for module metadata
- Provider bootstrap through `ModulesServiceProvider`
- Required module manifests with fail-fast schema validation
- Path normalization to prevent accidental path escaping from module boundaries
- PHPUnit, PHPStan, PHP-CS-Fixer, and GitHub Actions integration

## Requirements

- PHP 8.1+
- Composer
- A PSR-11-compatible container if you use `ModulesServiceProvider`

## Installation

```bash
composer require memran/marwa-module
```

## Quick Start

```php
<?php

declare(strict_types=1);

use Marwa\Module\ModuleBuilder;
use Marwa\Module\ModuleRegistry;
use Marwa\Module\ModuleRepository;

$modulesPath = __DIR__ . '/modules';
$cacheFile = __DIR__ . '/storage/cache/modules.php';

$repository = new ModuleRepository($modulesPath, $cacheFile);
$registry = new ModuleRegistry($repository);
$builder = new ModuleBuilder($registry);

$user = $builder->current('user');

$user->slug();
$user->routes('http');
$user->path('views');
$user->migrations();
```

## Module Layout

```text
project-root/
  modules/
    User/
      manifest.php
      routes/
        http.php
      src/
      resources/
```

Example `manifest.php`:

```php
<?php

declare(strict_types=1);

return [
    'name' => 'User Module',
    'slug' => 'user',
    'version' => '1.0.0',
    'providers' => [
        App\Modules\User\UserServiceProvider::class,
    ],
    'paths' => [
        'views' => 'resources/views',
    ],
    'routes' => [
        'http' => 'routes/http.php',
    ],
    'migrations' => [
        'database/migrations/2026_01_01_000000_create_users_table.php',
    ],
];
```

The library also accepts `manifest.json` with the same structure.

Manifest rules:

- A module directory must contain exactly one manifest file: `manifest.php` or `manifest.json`
- Directories without a manifest are ignored during discovery
- A manifest must define a non-empty string `slug`
- `providers` and `migrations` must be arrays of non-empty strings
- `paths` and `routes` must be maps with non-empty string keys and values
- Duplicate module slugs across discovered modules are rejected

## Service Provider Bootstrap

If your application container supports `add()` or `set()` and `addServiceProvider()`, you can register the package in one step:

```php
<?php

declare(strict_types=1);

use Marwa\Module\ModulesServiceProvider;

$provider = new ModulesServiceProvider(
    __DIR__ . '/modules',
    __DIR__ . '/storage/cache/modules.php'
);

$provider->register($app);
```

## Public API Overview

- `ModuleRepository`: scans module directories and optionally persists cache files.
- `ModuleRegistry`: keeps discovered modules in memory and resolves modules by slug or path.
- `ModuleBuilder`: high-level lookup API returning `ModuleHandle` instances.
- `Module`: immutable module metadata wrapper.
- `ModulesServiceProvider`: registers the repository, registry, builder, and module providers.

## Configuration Notes

- Keep the cache file in an application-controlled writable directory.
- A directory is only discovered as a module when it contains a valid manifest.
- Module paths declared in manifests are treated as relative to the module root.
- Absolute paths and `..` traversal segments are ignored when resolving module asset paths.
- Provider classes declared in manifests must exist and implement `Marwa\Module\Contracts\ModuleServiceProviderInterface`.
- If both `manifest.php` and `manifest.json` exist in the same module directory, discovery fails with an exception.

## Development

Install dependencies:

```bash
composer install
```

Available scripts:

```bash
composer test
composer test:coverage
composer analyse
composer lint
composer fix
composer ci
```

## Testing

- Test runner: PHPUnit
- Coverage command: `composer test:coverage`
- Test files live in `tests/`
- Fixture-backed module examples live in `tests/Fixtures/`

## Static Analysis And Linting

- Static analysis: PHPStan via `phpstan.neon.dist`
- Coding style: PHP-CS-Fixer via `.php-cs-fixer.dist.php`
- CI workflow: `.github/workflows/ci.yml`

## Production Notes

- Treat manifest files and cache file locations as trusted application assets.
- Prefer writing cache files under `storage/` or another private writable directory.
- If a cache file is corrupted, the repository falls back to a fresh filesystem scan.
- Invalid, ambiguous, or duplicate manifests fail fast with descriptive runtime exceptions.

## Contributing

- Keep changes small and focused.
- Add or update PHPUnit coverage for behavior changes.
- Run `composer ci` before opening a pull request.
- Keep documentation aligned with actual behavior and scripts.

## Release Checklist

1. Run `composer ci`
2. Review public API changes and backward compatibility
3. Update README or examples if usage changed
4. Tag and publish the package

## License

MIT
