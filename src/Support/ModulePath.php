<?php

declare(strict_types=1);

namespace Marwa\Module\Support;

final class ModulePath
{
    public static function join(string $basePath, string $relativePath): ?string
    {
        $relativePath = trim($relativePath);
        if ($relativePath === '' || str_contains($relativePath, "\0") || self::isAbsolute($relativePath)) {
            return null;
        }

        $normalizedBase = self::normalize($basePath);
        $normalizedPath = self::normalize($normalizedBase . '/' . $relativePath);

        return self::isWithinBasePath($normalizedPath, $normalizedBase)
              ? self::toPlatformPath($normalizedPath)
              : null;
    }

    public static function isWithinBasePath(string $path, string $basePath): bool
    {
        $normalizedPath = self::normalize($path);
        $normalizedBase = self::normalize($basePath);

        return $normalizedPath === $normalizedBase
              || str_starts_with($normalizedPath, $normalizedBase . '/');
    }

    public static function normalize(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        if ($path === '') {
            return '.';
        }

        $prefix = '';
        if (preg_match('/^[A-Za-z]:\//', $path) === 1) {
            $prefix = substr($path, 0, 2);
            $path = substr($path, 2);
        } elseif (str_starts_with($path, '//')) {
            $prefix = '//';
            $path = substr($path, 2);
        } elseif (str_starts_with($path, '/')) {
            $prefix = '/';
            $path = ltrim($path, '/');
        }

        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                if ($segments !== [] && end($segments) !== '..') {
                    array_pop($segments);
                    continue;
                }

                if ($prefix === '') {
                    $segments[] = $segment;
                }

                continue;
            }

            $segments[] = $segment;
        }

        $normalized = implode('/', $segments);

        if ($prefix === '') {
            return $normalized === '' ? '.' : $normalized;
        }

        if ($normalized === '') {
            return $prefix;
        }

        return $prefix . $normalized;
    }

    private static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/')
              || str_starts_with($path, '\\')
              || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1;
    }

    private static function toPlatformPath(string $path): string
    {
        return DIRECTORY_SEPARATOR === '/'
              ? $path
              : str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}
