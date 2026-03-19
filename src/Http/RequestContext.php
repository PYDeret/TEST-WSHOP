<?php

declare(strict_types=1);

namespace App\Http;

class RequestContext
{
    private static ?object $jwtPayload = null;

    public static function setJwtPayload(object $payload): void
    {
        self::$jwtPayload = $payload;
    }

    public static function getJwtPayload(): ?object
    {
        return self::$jwtPayload;
    }

    public static function reset(): void
    {
        self::$jwtPayload = null;
    }
}
