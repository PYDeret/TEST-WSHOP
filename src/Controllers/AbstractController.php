<?php

declare(strict_types=1);

namespace App\Controllers;

abstract class AbstractController
{
    /**
     * Parses the raw JSON request body into an associative array.
     *
     * @return array<string, mixed>
     */
    protected function parseBody(): array
    {
        $body = json_decode(file_get_contents('php://input') ?: '{}', true);

        return is_array($body) ? $body : [];
    }
}
