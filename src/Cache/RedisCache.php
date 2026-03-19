<?php

declare(strict_types=1);

namespace App\Cache;

use Redis;
use RuntimeException;

readonly class RedisCache implements CacheInterface
{
    private Redis $redis;

    public function __construct()
    {
        $host = $_ENV['REDIS_HOST'] ?? 'redis';
        $port = (int)($_ENV['REDIS_PORT'] ?? 6379);
        $password = $_ENV['REDIS_PASSWORD'] ?? null;

        $this->redis = new Redis();

        if (!$this->redis->connect($host, $port, 2.0)) {
            throw new RuntimeException('Redis connection failed');
        }

        if ($password) {
            $this->redis->auth($password);
        }
    }

    public function get(string $key): mixed
    {
        $value = $this->redis->get($key);

        if ($value === false) {
            return null;
        }

        return json_decode($value, true);
    }

    public function set(string $key, mixed $value, int $ttl = 60): void
    {
        $this->redis->setEx($key, $ttl, json_encode($value));
    }

    public function delete(string $key): void
    {
        $this->redis->del($key);
    }

    public function deleteByPattern(string $pattern): void
    {
        $keys = $this->redis->keys($pattern);

        if (!empty($keys)) {
            $this->redis->del($keys);
        }
    }
}
