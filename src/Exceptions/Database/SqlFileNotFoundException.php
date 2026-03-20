<?php

declare(strict_types=1);

namespace App\Exceptions\Database;

use App\Exceptions\HttpException;

class SqlFileNotFoundException extends HttpException
{
    public function __construct(string $filepath)
    {
        parent::__construct("SQL file not found : $filepath", 404);
    }
}
