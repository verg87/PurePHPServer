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
    public static function setIconPath(string $path): void
    {
        if ($path !== "" && file_exists($path)) {
            $conf = parse_ini_file(static::PATH, true);
            $conf["icon"]["path"] = $path;

            static::write_conf_file(static::PATH, $conf);
        }
    }

    public static function getIconPath(): string
    {
        $conf = parse_ini_file(static::PATH, true);
        
        return str_starts_with($conf["icon"]["path"], ".") 
            ? __DIR__ . substr($conf["icon"]["path"], 1) 
            : __DIR__ . $conf["icon"]["path"];
    }
}

// var_dump(Configuration::getIconPath());