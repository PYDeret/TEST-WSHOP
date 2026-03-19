<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Response;
use App\Services\StoreService;

final class StoreController extends AbstractController
{
    public function __construct(private readonly StoreService $service)
    {
    }

    public function findAll(): void
    {
        $result = $this->service->list($_GET);

        Response::json($result);
    }

    /**
     * @param array<string, string> $params
     */
    public function findById(array $params): void
    {
        $result = $this->service->show((int)$params['id']);

        Response::json($result);
    }

    public function post(): void
    {
        $body = $this->parseBody();
        $result = $this->service->create($body);

        Response::json($result, 201);
    }

    /**
     * @param array<string, string> $params
     */
    public function put(array $params): void
    {
        $body = $this->parseBody();
        $result = $this->service->update((int)$params['id'], $body);

        Response::json($result);
    }

    /**
     * @param array<string, string> $params
     */
    public function patch(array $params): void
    {
        $body = $this->parseBody();
        $result = $this->service->patch((int)$params['id'], $body);

        Response::json($result);
    }

    /**
     * @param array<string, string> $params
     */
    public function delete(array $params): void
    {
        $this->service->delete((int)$params['id']);

        Response::json(null, 204);
    }
}
