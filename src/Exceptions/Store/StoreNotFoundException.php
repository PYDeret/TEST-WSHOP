<?php

declare(strict_types=1);

namespace App\Exceptions\Store;

use App\Exceptions\HttpException;

class StoreNotFoundException extends HttpException
{
    public function __construct(int $id)
    {
        parent::__construct("Store #$id not found.", 404);
    }
}
