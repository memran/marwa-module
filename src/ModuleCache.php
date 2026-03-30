<?php

declare(strict_types=1);

namespace Marwa\Module;

use RuntimeException;

/**
 * Minimal file-based cache for module manifests.
 * Stores a plain PHP array in a single file.
 */
final class ModuleCache
{
    /**
     * Load cached data or return null if missing/unreadable.
     *
     * @return array<string, mixed>|null
     */
    public static function load(string $cacheFile): ?array
    {
        if (!is_file($cacheFile)) {
            return null;
        }
        if (!is_readable($cacheFile)) {
            throw new RuntimeException("Cache file [$cacheFile] is not readable.");
        }

        /** @var array<string, mixed>|null $data */
        $data = include $cacheFile;
        return is_array($data) ? $data : null;
    }

    /**
     * Save array to a PHP file that returns the array.
     *
     * @param array<string, mixed> $data
     */
    public static function save(string $cacheFile, array $data): void
    {
        $dir = \dirname($cacheFile);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException("Unable to create cache directory [$dir].");
            }
        }
        $export = var_export($data, true);
        $php    = "<?php\n\ndeclare(strict_types=1);\n\nreturn {$export};\n";

        if (file_put_contents($cacheFile, $php, LOCK_EX) === false) {
            throw new RuntimeException("Unable to write cache file [$cacheFile].");
        }
    }

    public static function clear(string $cacheFile): void
    {
        if (is_file($cacheFile) && !unlink($cacheFile)) {
            throw new RuntimeException("Unable to remove cache file [$cacheFile].");
        }
    }
}
