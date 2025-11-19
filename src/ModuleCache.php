<?php

declare(strict_types=1);

namespace Marwa\Module;

/**
 * Minimal file-based cache for module manifests.
 * Stores a plain PHP array in a single file.
 */
final class ModuleCache
{
      /**
       * Load cached data or return null if missing/unreadable.
       *
       * @return array|null
       */
      public static function load(string $cacheFile): ?array
      {
            if (!is_file($cacheFile)) {
                  return null;
            }
            /** @var array|null $data */
            $data = @include $cacheFile;
            return is_array($data) ? $data : null;
      }

      /**
       * Save array to a PHP file that returns the array.
       */
      public static function save(string $cacheFile, array $data): void
      {
            $dir = \dirname($cacheFile);
            if (!is_dir($dir)) {
                  @mkdir($dir, 0777, true);
            }
            $export = var_export($data, true);
            $php    = "<?php\nreturn {$export};\n";
            file_put_contents($cacheFile, $php, LOCK_EX);
      }

      public static function clear(string $cacheFile): void
      {
            if (is_file($cacheFile)) {
                  @unlink($cacheFile);
            }
      }
}
