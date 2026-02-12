<?php

declare(strict_types=1);

namespace Server\Helpers;

class FilesHelper
{
    public static function findFileByWildcard(string $basePath): bool|string {
        $matches = glob($basePath . '.*');

        if ($matches !== false && count($matches) > 0) {
            foreach ($matches as $match) {
                if (is_file($match)) {
                    return $match;
                }
            }
        }
        return false;
    }
}