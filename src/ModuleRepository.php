<?php

declare(strict_types=1);

namespace Marwa\Module;

use Marwa\Module\ModuleCache;
use RuntimeException;

final class ModuleRepository
{
      /** @var string[] */
      private array $modulePaths;

      public function __construct(
            string|array $modulesPath,
            private ?string $cacheFile = null
      ) {
            $this->modulePaths = array_map(
                  fn(string $p) => rtrim($p, DIRECTORY_SEPARATOR),
                  (array) $modulesPath
            );
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
                  if (is_array($cached) && !empty($cached['modules'])) {
                        return $this->hydrateFromCache($cached['modules']);
                  }
            }

            $rows = $this->scanFilesystem();

            if ($this->cacheFile) {
                  ModuleCache::save($this->cacheFile, [
                        'generated_at' => time(),
                        'modules'      => $rows,
                  ]);
            }

            return $this->hydrateFromCache($rows);
      }

      /**
       * @return array<string, array{slug:string,basePath:string,manifest:array}>
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
                        $manifest  = $this->loadManifest($moduleDir);
                        //$manifest  = $this->validator->validate($manifest, $moduleDir, $dirName);

                        $slug = $manifest['slug'] ?? $dirName;

                        $result[$slug] = [
                              'slug'     => $slug,
                              'basePath' => $moduleDir,
                              'manifest' => $manifest,
                        ];
                  }
            }

            return $result;
      }

      private function loadManifest(string $moduleDir): array
      {
            $php  = $moduleDir . DIRECTORY_SEPARATOR . 'manifest.php';
            $json = $moduleDir . DIRECTORY_SEPARATOR . 'manifest.json';

            if (is_file($php)) {
                  /** @var mixed $data */
                  $data = require $php;
                  if (!is_array($data)) {
                        throw new RuntimeException("manifest.php must return array in [$moduleDir]");
                  }
                  return $data;
            }

            if (is_file($json)) {
                  $raw = file_get_contents($json);
                  $data = $raw !== false ? json_decode($raw, true) : null;
                  if (!is_array($data)) {
                        throw new RuntimeException("manifest.json must be valid array in [$moduleDir]");
                  }
                  return $data;
            }

            return []; // minimal; validator will fill defaults
      }

      /**
       * @param array<string, array{slug:string,basePath:string,manifest:array}> $rows
       * @return array<string, Module>
       */
      private function hydrateFromCache(array $rows): array
      {
            $out = [];
            foreach ($rows as $slug => $row) {
                  $out[$slug] = new Module($row['slug'], $row['basePath'], $row['manifest']);
            }
            return $out;
      }
}
