<?php

namespace ddruganov\TransactionalFileSystem\helpers;

final class PathHelper
{
    public static function normalize(string $path)
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = [];

        foreach ($parts as $part) {

            if ($part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($absolutes);
                continue;
            }

            $absolutes[] = $part;
        }

        return join(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function concat(string $left, string $right)
    {
        return self::normalize(
            join(DIRECTORY_SEPARATOR, [$left, $right])
        );
    }

    public static function removePrefix(string $prefix, string $path)
    {
        $normalized = self::normalize($path);

        if (!str_starts_with($normalized, $prefix)) {
            return $normalized;
        }

        $withoutPrefix = substr($normalized, strlen($prefix));
        return self::normalize($withoutPrefix);
    }

    public static function addPrefix(string $prefix, string $path)
    {
        return join(DIRECTORY_SEPARATOR, [$prefix, $path]);
    }

    public static function getFolder(string $path)
    {
        return join(DIRECTORY_SEPARATOR, explode(DIRECTORY_SEPARATOR, $path, -1));
    }

    public static function getPathHeadAndTail(string $path)
    {
        return explode(DIRECTORY_SEPARATOR, $path, 2) + [1 => null];
    }
}
