<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Exceptions\HttpException;

class AuthorizationHeaderMissingException extends HttpException
{
    public function __construct(string $message = 'Missing Authorization header.')
    {
        parent::__construct($message, 401);
    }
}
