<?php
// src/ModuleRepository.php

declare(strict_types=1);

namespace Marwa\Module;

use RuntimeException;

/**
 * Discovers modules from one or more filesystem directories,
 * but uses ModuleCacheAdapter to avoid scanning every time.
 */
class ModuleRepository
{
      /**
       * @param string|array<int,string> $modulesPath
       */
      public function __construct(
            private string|array $modulesPath
      ) {
            $this->modulesPath = (array) $modulesPath;
            $this->modulesPath = array_map(
                  static fn(string $p) => rtrim($p, DIRECTORY_SEPARATOR),
                  $this->modulesPath
            );
      }

      /**
       * @return Module[]
       */
      public function all(): array
      {
            // 1. use in-memory cache if available
            if (ModuleCacheAdapter::hasCache()) {
                  return $this->hydrateFromCache(ModuleCacheAdapter::getCache());
            }

            // 2. otherwise scan all module paths
            $cached = [];

            foreach ($this->modulesPath as $path) {
                  foreach ($this->scanFilesystem($path) as $slug => $row) {
                        // last one wins if same slug appears in multiple paths
                        $cached[$slug] = $row;
                  }
            }

            // 3. store to static cache
            ModuleCacheAdapter::setCache($cached);

            // 4. return hydrated module objects
            return $this->hydrateFromCache($cached);
      }

      /**
       * Scan a single directory for modules.
       *
       * @return array<string, array{slug:string,basePath:string,manifest:array}>
       */
      private function scanFilesystem(string $modulesPath): array
      {
            if (!is_dir($modulesPath)) {
                  return [];
            }

            $result = [];
            $dir = new \DirectoryIterator($modulesPath);

            foreach ($dir as $fileInfo) {
                  if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                        continue;
                  }

                  $moduleDir = $fileInfo->getPathname();
                  $manifest  = $this->loadManifest($moduleDir);

                  $slug = $manifest['slug'] ?? $fileInfo->getBasename();

                  $result[$slug] = [
                        'slug'     => $slug,
                        'basePath' => $moduleDir,
                        'manifest' => $manifest,
                  ];
            }
            
            return $result;
      }

      /**
       * Hydrate simple cached arrays into real Module objects.
       *
       * @param array<string, array{slug:string,basePath:string,manifest:array}> $cached
       * @return Module[]
       */
      private function hydrateFromCache(array $cached): array
      {
            $modules = [];
            foreach ($cached as $slug => $row) {
                  $modules[$slug] = new Module(
                        $row['slug'],
                        $row['basePath'],
                        $row['manifest']
                  );
            }
            return $modules;
      }

      /**
       * Try manifest.php then manifest.json
       */
      private function loadManifest(string $moduleDir): array
      {
            $php  = $moduleDir . DIRECTORY_SEPARATOR . 'manifest.php';
            $json = $moduleDir . DIRECTORY_SEPARATOR . 'manifest.json';

            if (is_file($php)) {
                  /** @var array $data */
                  $data = require $php;
                  if (!is_array($data)) {
                        throw new RuntimeException("manifest.php must return array in [$moduleDir]");
                  }
                  return $data;
            }

            if (is_file($json)) {
                  $data = json_decode((string) file_get_contents($json), true);
                  if (!is_array($data)) {
                        throw new RuntimeException("manifest.json must be valid json in [$moduleDir]");
                  }
                  return $data;
            }

            // minimal manifest
            return [];
      }
}
