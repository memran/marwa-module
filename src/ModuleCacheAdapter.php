<?php
// src/ModuleCacheAdapter.php

declare(strict_types=1);

namespace Marwa\Module;

/**
 * Tiny, in-memory (static) cache for module manifest data.
 *
 * Goal:
 *  - avoid re-scanning /modules on every call
 *  - keep it framework-agnostic
 *  - cache ONLY raw manifest data (slug, basePath, manifest array)
 *
 * This is per-process. If you need cross-process persistence,
 * wrap this with a file/psr-16 layer.
 */
final class ModuleCacheAdapter
{
    /**
     * @var array<string, array{slug:string,basePath:string,manifest:array}>
     */
    private static array $cache = [];

    /**
     * Check if we already have cached module data.
     */
    public static function hasCache(): bool
    {
        return !empty(self::$cache);
    }

    /**
     * @return array<string, array{slug:string,basePath:string,manifest:array}>
     */
    public static function getCache(): array
    {
        return self::$cache;
    }

    /**
     * @param array<string, array{slug:string,basePath:string,manifest:array}> $data
     */
    public static function setCache(array $data): void
    {
        self::$cache = $data;
    }

    /**
     * Clear cache (useful in dev or hot-reload).
     */
    public static function clear(): void
    {
        self::$cache = [];
    }
}
