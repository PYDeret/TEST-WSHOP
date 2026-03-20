<?php

declare(strict_types=1);

namespace App\Exceptions\Auth;

use App\Exceptions\HttpException;

class EmailAlreadyExistingException extends HttpException
{
    public function __construct(string $message = 'Email already existing.')
    {
        parent::__construct($message, 409);
    }
}
