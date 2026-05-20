<?php

declare(strict_types=1);

namespace Marwa\Module;

use JsonException;
use Marwa\Module\Contracts\ModuleRepositoryInterface;
use Marwa\Module\Exception\InvalidManifestException;
use Marwa\Support\Arr;

final class ModuleRepository implements ModuleRepositoryInterface
{
    /** @var string[] */
    private array $modulePaths;

    /**
     * @param string|array<int, string> $modulesPath
     */
    public function __construct(
        string|array $modulesPath,
        private ?string $cacheFile = null
    ) {
        $paths = [];
        foreach ((array) $modulesPath as $path) {
            $path = rtrim(trim($path), DIRECTORY_SEPARATOR);
            if ($path === '') {
                continue;
            }

            $paths[] = $path;
        }

        $this->modulePaths = array_values(array_unique($paths));
    }

    /**
     * Load modules. If cacheFile is set and not forced to refresh, use it.
     * To force refresh: pass $forceRefresh=true to scan().
     *
     * @return array<string, Module>
     */
    public function all(bool $forceRefresh = false): array
    {
        if ($this->cacheFile && !$forceRefresh) {
            $cached = ModuleCache::load($this->cacheFile);
            $cachedModules = Arr::get($cached ?? [], 'modules', []);
            if (is_array($cachedModules) && $cachedModules !== []) {
                try {
                    return $this->hydrateRows($cachedModules);
                } catch (InvalidManifestException) {
                    // Corrupted cache should fall back to a fresh scan.
                }
            }
        }

        $rows = $this->scanFilesystem();

        if ($this->cacheFile) {
            ModuleCache::save($this->cacheFile, [
                  'generated_at' => time(),
                  'modules'      => $rows,
            ]);
        }

        return $this->hydrateRows($rows);
    }

    /**
     * @return array<string, array{slug:string,basePath:string,manifest:array<string, mixed>}>
     */
    private function scanFilesystem(): array
    {
        $result = [];

        foreach ($this->modulePaths as $path) {
            if (!is_dir($path)) {
                continue;
            }
            $dir = new \DirectoryIterator($path);
            foreach ($dir as $fi) {
                if ($fi->isDot() || !$fi->isDir()) {
                    continue;
                }
                $moduleDir = $fi->getPathname();
                $dirName   = $fi->getBasename();
                if (!$this->hasManifest($moduleDir)) {
                    continue;
                }

                $manifest  = $this->validateManifest(
                    $this->loadManifest($moduleDir),
                    $dirName,
                    $moduleDir
                );
                $slug = (string) $manifest['slug'];

                if (isset($result[$slug])) {
                    throw new InvalidManifestException(
                        "Duplicate module slug [$slug] found in [$moduleDir]."
                    );
                }

                $result[$slug] = [
                      'slug'     => $slug,
                      'basePath' => $moduleDir,
                      'manifest' => $manifest,
                ];
            }
        }

        ksort($result);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadManifest(string $moduleDir): array
    {
        $php  = $moduleDir . DIRECTORY_SEPARATOR . 'manifest.php';
        $json = $moduleDir . DIRECTORY_SEPARATOR . 'manifest.json';

        if (is_file($php) && is_file($json)) {
            throw new InvalidManifestException(
                "Module [$moduleDir] cannot define both manifest.php and manifest.json."
            );
        }

        if (is_file($php)) {
            /** @var mixed $data */
            $data = require $php;
            if (!is_array($data)) {
                throw new InvalidManifestException("manifest.php must return an array in [$moduleDir].");
            }
            return $data;
        }

        if (is_file($json)) {
            $raw = file_get_contents($json);
            if ($raw === false) {
                throw new InvalidManifestException("manifest.json could not be read in [$moduleDir].");
            }

            try {
                /** @var mixed $data */
                $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new InvalidManifestException(
                    "manifest.json must contain valid JSON in [$moduleDir].",
                    0,
                    $exception
                );
            }

            if (!is_array($data)) {
                throw new InvalidManifestException("manifest.json must decode to an array in [$moduleDir].");
            }
            return $data;
        }

        return [];
    }

    /**
     * @param array<string, mixed> $manifest
     * @return array<string, mixed>
     */
    private function validateManifest(array $manifest, string $dirName, string $moduleDir): array
    {
        $slug = Arr::get($manifest, 'slug');
        if (!is_string($slug) || trim($slug) === '') {
            throw new InvalidManifestException("Module [$moduleDir] must define a non-empty string slug.");
        }

        $slug = trim($slug);
        $name = Arr::get($manifest, 'name', $slug);
        $version = Arr::get($manifest, 'version');
        $providers = Arr::get($manifest, 'providers', []);
        $paths = Arr::get($manifest, 'paths', []);
        $routes = Arr::get($manifest, 'routes', []);
        $migrations = Arr::get($manifest, 'migrations', []);

        if ($name !== null && (!is_string($name) || trim($name) === '')) {
            throw new InvalidManifestException("Module [$moduleDir] has an invalid [name] value.");
        }
        if ($version !== null && (!is_string($version) || trim($version) === '')) {
            throw new InvalidManifestException("Module [$moduleDir] has an invalid [version] value.");
        }

        $normalizedProviders = $this->validateStringList($providers, 'providers', $moduleDir);
        $normalizedPaths = $this->validateStringMap($paths, 'paths', $moduleDir);
        $normalizedRoutes = $this->validateStringMap($routes, 'routes', $moduleDir);
        $normalizedMigrations = $this->validateStringList($migrations, 'migrations', $moduleDir);

        return array_merge($manifest, [
              'name'       => is_string($name) ? trim($name) : $dirName,
              'slug'       => $slug,
              'version'    => is_string($version) ? trim($version) : null,
              'providers'  => $normalizedProviders,
              'paths'      => $normalizedPaths,
              'routes'     => $normalizedRoutes,
              'migrations' => $normalizedMigrations,
        ]);
    }

    /**
     * @param array<string, mixed> $rows
     * @return array<string, Module>
     */
    private function hydrateRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $slug => $row) {
            if (!is_array($row)) {
                throw new InvalidManifestException('Cached module rows must be arrays.');
            }

            $resolvedSlug = $row['slug'] ?? $slug;
            $basePath = $row['basePath'] ?? null;
            $manifest = $row['manifest'] ?? null;

            if (!is_string($resolvedSlug) || $resolvedSlug === '') {
                throw new InvalidManifestException('Cached module rows must contain a non-empty slug.');
            }
            if (!is_string($basePath) || $basePath === '') {
                throw new InvalidManifestException("Module [$resolvedSlug] is missing a valid base path.");
            }
            if (!is_array($manifest)) {
                throw new InvalidManifestException("Module [$resolvedSlug] is missing a valid manifest.");
            }

            $normalizedManifest = $this->validateManifest($manifest, basename($basePath), $basePath);
            $out[$resolvedSlug] = new Module($resolvedSlug, $basePath, $normalizedManifest);
        }
        return $out;
    }

    private function hasManifest(string $moduleDir): bool
    {
        return is_file($moduleDir . DIRECTORY_SEPARATOR . 'manifest.php')
            || is_file($moduleDir . DIRECTORY_SEPARATOR . 'manifest.json');
    }

    /**
     * @param mixed $value
     * @return string[]
     */
    private function validateStringList(mixed $value, string $field, string $moduleDir): array
    {
        if (!is_array($value)) {
            throw new InvalidManifestException("Module [$moduleDir] field [$field] must be an array.");
        }

        $normalized = [];
        foreach ($value as $entry) {
            if (!is_string($entry) || trim($entry) === '') {
                throw new InvalidManifestException(
                    "Module [$moduleDir] field [$field] must contain only non-empty strings."
                );
            }

            $normalized[] = trim($entry);
        }

        return array_values(array_unique($normalized));
    }

    /**
     * @param mixed $value
     * @return array<string, string>
     */
    private function validateStringMap(mixed $value, string $field, string $moduleDir): array
    {
        if (!is_array($value)) {
            throw new InvalidManifestException("Module [$moduleDir] field [$field] must be an array.");
        }

        $normalized = [];
        foreach ($value as $key => $entry) {
            if (!is_string($key) || trim($key) === '') {
                throw new InvalidManifestException(
                    "Module [$moduleDir] field [$field] must use non-empty string keys."
                );
            }

            if (!is_string($entry) || trim($entry) === '') {
                throw new InvalidManifestException(
                    "Module [$moduleDir] field [$field] must contain only non-empty string values."
                );
            }

            $normalized[trim($key)] = trim($entry);
        }

        return $normalized;
    }
}
