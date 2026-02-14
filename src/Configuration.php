<?php

declare(strict_types=1);

namespace Server;

class Configuration
{
    CONST PATH = __DIR__ . "\..\pure.conf";
    protected static function write_conf_file(string $file, array $data): int|bool
    {
        $content = "";

        foreach ($data as $key => $value) {
            $content .= "\n[$key]\n";

            foreach ($value as $subKey => $subValue) {
                $content .= "$subKey = " . (is_numeric($subValue) ? $subValue : '"' . $subValue . '"') . "\n";
            }
        }

        return file_put_contents($file, trim($content));
    }

    protected static function set(string $path, string $section, string $key): void
    {
        if ($path !== "" && file_exists($path)) {
            $conf = parse_ini_file(static::PATH, true);
            $conf[$section][$key] = $path;

            static::write_conf_file(static::PATH, $conf);
        }
    }

    protected static function get(string $section, string $key): string
    {
        $conf = parse_ini_file(static::PATH, true);
        
        return str_starts_with($conf[$section][$key], ".") 
            ? __DIR__ . substr($conf[$section][$key], 1) 
            : __DIR__ . $conf[$section][$key];
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
}

// var_dump(Configuration::getIconPath());