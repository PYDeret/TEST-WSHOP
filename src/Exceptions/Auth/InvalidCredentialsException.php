<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Exceptions\HttpException;

class InvalidCredentialsException extends HttpException
{
    public function __construct(string $message = 'Invalid credentials.')
    {
        parent::__construct($message, 401);
    }
}
