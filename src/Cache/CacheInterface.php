<?php

declare(strict_types=1);

namespace App\Cache;

interface CacheInterface
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttl = 60): void;

    public function delete(string $key): void;

    public function deleteByPattern(string $pattern): void;
}
