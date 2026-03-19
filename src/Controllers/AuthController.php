<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Services\AuthService;

class AuthController extends AbstractController
{
    public function __construct(private readonly AuthService $service)
    {
    }

    public function register(): void
    {
        $body = $this->parseBody();
        $result = $this->service->register($body);

        Response::json(['data' => $result], 201);
    }

    public function login(): void
    {
        $body = $this->parseBody();
        $result = $this->service->login($body);

        Response::json(['data' => $result]);
    }
}
