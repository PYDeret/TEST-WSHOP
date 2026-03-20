<?php

declare(strict_types=1);

namespace App\Exceptions\Redis;

use App\Exceptions\HttpException;

class RedisConnectionFailedException extends HttpException
{
    public function __construct(string $message = 'Redis connection failed.')
    {
        parent::__construct($message, 404);
    }
}
