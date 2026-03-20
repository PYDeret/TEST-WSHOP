<?php

declare(strict_types=1);

namespace App\Database;

use App\Exceptions\Database\SqlFileNotFoundException;

class SqlLoader
{
    public static function load(string $path): string
    {
        $fullPath = dirname(__DIR__) . '/Queries/' . $path;

        if (!file_exists($fullPath)) {
            throw new SqlFileNotFoundException($fullPath);
        }

        return (string) file_get_contents($fullPath);
    }
}
