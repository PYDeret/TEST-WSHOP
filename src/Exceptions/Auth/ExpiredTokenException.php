<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Exceptions\HttpException;

class ExpiredTokenException extends HttpException
{
    public function __construct(string $message = 'Invalid or expired token.')
    {
        parent::__construct($message, 401);
    }
}
