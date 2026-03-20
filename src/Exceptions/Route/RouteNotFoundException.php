<?php

declare(strict_types=1);

namespace App\Exceptions\Route;

use App\Exceptions\HttpException;

class RouteNotFoundException extends HttpException
{
    public function __construct(string $message = 'Route not found.')
    {
        parent::__construct($message, 404);
    }
}
