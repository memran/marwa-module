<?php

declare(strict_types=1);

namespace Marwa\Module;

use JsonException;
use Marwa\Module\Exception\InvalidManifestException;
use Marwa\Support\Arr;

final class ModuleRepository
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
            if (!is_string($path)) {
                continue;
            }

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
                $manifest  = $this->normalizeManifest(
                    $this->loadManifest($moduleDir),
                    $dirName
                );
                $slug = (string) $manifest['slug'];

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
    private function normalizeManifest(array $manifest, string $dirName): array
    {
        $slug = Arr::get($manifest, 'slug', $dirName);
        $name = Arr::get($manifest, 'name', $slug);
        $version = Arr::get($manifest, 'version');
        $providers = Arr::get($manifest, 'providers', []);
        $paths = Arr::get($manifest, 'paths', []);
        $routes = Arr::get($manifest, 'routes', []);
        $migrations = Arr::get($manifest, 'migrations', []);

        $normalizedProviders = [];
        if (is_array($providers)) {
            foreach ($providers as $provider) {
                if (!is_string($provider)) {
                    continue;
                }

                $provider = trim($provider);
                if ($provider === '') {
                    continue;
                }

                $normalizedProviders[] = $provider;
            }
        }

        return [
              'name'       => is_string($name) && $name !== '' ? $name : $dirName,
              'slug'       => is_string($slug) && $slug !== '' ? $slug : $dirName,
              'version'    => is_string($version) && $version !== '' ? $version : null,
              'providers'  => array_values(array_unique($normalizedProviders)),
              'paths'      => is_array($paths) ? $paths : [],
              'routes'     => is_array($routes) ? $routes : [],
              'migrations' => is_array($migrations) ? array_values($migrations) : [],
        ];
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

            $normalizedManifest = $this->normalizeManifest($manifest, basename($basePath));
            $out[$resolvedSlug] = new Module($resolvedSlug, $basePath, $normalizedManifest);
        }
        return $out;
    }
}
