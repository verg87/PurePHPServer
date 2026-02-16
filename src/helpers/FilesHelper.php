<?php

declare(strict_types=1);

namespace Server\Helpers;

class FilesHelper
{
    public static function findFileByWildcard(string $basePath): string {
        $matches = glob($basePath . '.*');

        if ($matches !== false && count($matches) > 0) {
            foreach ($matches as $match) {
                if (is_file($match)) {
                    return $match;
                }
            }
        }
        return $basePath;
    }

    public static function generateRandomFileName(int $length = 16): string {
        $randomString = bin2hex(random_bytes($length));  

        return substr($randomString, 0, $length);  
    }
}