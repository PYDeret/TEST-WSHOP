<?php

declare(strict_types=1);

namespace App\Exceptions\Database;

use App\Exceptions\HttpException;

class DatabaseConnectionFailedException extends HttpException
{
    public function __construct(string $message = 'Database connection failed.')
    {
        parent::__construct($message, 404);
    }
}
