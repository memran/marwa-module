# Repository Guidelines

## Project Structure & Module Organization
`src/` contains the library code under the `Marwa\Module\` PSR-4 namespace. Core types such as `Module`, `ModuleRegistry`, and `ModuleBuilder` live here, with contracts in `src/Contracts/`. `tests/` holds the automated test suite plus fixtures in `tests/Fixtures/` and container doubles in `tests/Container/`. `examples/` shows expected module layouts, manifests, and cache output for local experimentation.

## Build, Test, and Development Commands
Run `composer install` to install dependencies. Use `composer test` to execute the full test suite through `vendor/bin/testify`. If you want to inspect package metadata before publishing, run `composer validate`. For quick local checks while editing, `php vendor/bin/testify tests/ModuleBuilderTest.php` runs a single test file.

## Coding Style & Naming Conventions
Target PHP 8.1+ and keep `declare(strict_types=1);` at the top of PHP files. Follow the existing PSR-4 layout: one class per file, StudlyCase class names, and descriptive method names such as `findByPath()` or `routeFile()`. Tests use the `Marwa\Module\Tests` namespace. Match the current file formatting style in this repository, including brace placement and the project’s existing indentation, instead of reformatting unrelated files.

## Testing Guidelines
Tests are configured in [`phpunit.config.php`](/Users/memran/projects/php-projects/marwa-module/phpunit.config.php) and discovered from `tests/*Test.php` and `tests/*_test.php`. Add or update tests for every behavior change, especially around module discovery, manifests, registry lookups, and cache behavior. Prefer fixture-backed tests over mocking filesystem-heavy flows. Name tests by behavior, for example `test_it_scans_modules_directory_once`.

## Commit & Pull Request Guidelines
Recent history uses short, imperative commit messages such as `Update ModuleCache` and `Fixed Container`. Keep commits focused and concise; use the subject line to describe the change directly. Pull requests should include a brief summary, the reason for the change, any affected public API or manifest behavior, and the test command you ran. Include example fixture or usage updates when behavior visible in `examples/` changes.

## Configuration Notes
This package depends on a PSR-11-compatible container and expects module manifests under a modules directory. When adding fixtures or examples, keep both `manifest.php` and route file paths realistic so discovery and cache tests reflect production usage.
