<?php

declare(strict_types=1);

namespace Server;

class Configuration
{
    public const DEFAULT_PAGES_PATH = __DIR__ . "\pages";
    public const PUBLIC_PAGES_PATH = __DIR__ . "\..\public";
    public const ICONS_PATH = __DIR__ . "\..\icons";
    public const TMP_FILES_PATH = __DIR__ . "\\tmp";
    public const PATH = __DIR__ . "\..\pure.conf";
    public static function writeConfFile(string $file, array $data): int|bool
    {
        $content = "";

        foreach ($data as $key => $value) {
            $content .= "\n[$key]\n";

            foreach ($value as $subKey => $subValue) {
                if (gettype($subValue) !== "array") {
                    $content .= "$subKey = " . (is_numeric($subValue) ? $subValue : '"' . $subValue . '"') . "\n";

                    continue;
                }

                foreach ($subValue as $subSubValue) {
                    $content .= "$subKey" . "[]" . " = " . (is_numeric($subSubValue) ? $subSubValue : '"' . $subSubValue . '"') . "\n";
                }
            }
        }

        return file_put_contents($file, trim($content));
    }

    protected static function set(mixed $data, string $section, string $key): void
    {
        $type = gettype($data);
        $validPath = ($type === "string" && $data !== "" && file_exists($data)) || $type === "array";

        if (!in_array($type, ["array", "string"]) || !$validPath) {
            return;
        }

        $conf = parse_ini_file(static::PATH, true);
        if (!$conf) {
            return;
        }

        $conf[$section][$key] = $data;

        static::writeConfFile(static::PATH, $conf);
    }

    protected static function get(string $section, string $key): mixed
    {
        $conf = parse_ini_file(static::PATH, true);
        if (!$conf) {
            return [];
        }
        
        return $conf[$section][$key];
    }

    public static function setIconPath(string $path): void
    {
        static::set($path, "Icon", "path");
    }

    public static function getIconPath(): string
    {
        return static::get("Icon", "path");
    }

    public static function setDefaultPagePath(string $path): void
    {
        static::set($path, "DefaultPage", "path");
    }

    public static function getDefaultPagePath(): string
    {
        return static::get("DefaultPage", "path");
    }

    public static function setUserAllowedFileFormats(array $formats): void
    {
        static::set($formats, "UserAllowedFileFormats", "formats");
    }

    public static function getUserAllowedFileFormats(): array
    {
        return static::get("UserAllowedFileFormats", "formats");
    }
}