<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Exceptions\UnauthorizedException;
use App\Http\RequestContext;
use App\Services\AuthService;

readonly class AuthMiddleware
{
    public function __construct(private AuthService $authService)
    {
    }

    public function __invoke(): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!str_starts_with($header, 'Bearer ')) {
            throw new UnauthorizedException('Missing Authorization header');
        }

        $token = substr($header, 7);
        $payload = $this->authService->verifyToken($token);

        RequestContext::setJwtPayload($payload);
    }
}
