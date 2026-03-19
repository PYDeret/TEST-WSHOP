<?php

declare(strict_types=1);

namespace App\Cache;

class NullCache implements CacheInterface
{
    public function get(string $key): null
    {
        return null;
    }

    public function set(string $key, mixed $value, int $ttl = 60): void
    {
    }

    public function delete(string $key): void
    {
    }

    public function deleteByPattern(string $pattern): void
    {
    }
}
