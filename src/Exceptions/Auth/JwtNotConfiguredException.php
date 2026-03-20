<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Exceptions\HttpException;

class JwtNotConfiguredException extends HttpException
{
    public function __construct(string $message = 'JWT_SECRET must be configured.')
    {
        parent::__construct($message, 404);
    }
}
