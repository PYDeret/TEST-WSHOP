<?php

declare(strict_types=1);

namespace App\Router;

use App\Exceptions\HttpException;

class Router
{
    /**
     * @var list<array{
     *   method: string,
     *   regex: string,
     *   handler: callable(array<string, string>): void,
     *   middleware: list<callable(): void>
     * }>
     */
    private array $routes = [];

    /**
     * @param callable(array<string, string>): void $handler
     * @param list<callable(): void> $middleware
     */
    public function add(string $method, string $pattern, callable $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'regex' => '#^' . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern) . '$#',
            'handler' => $handler,
            'middleware' => array_values($middleware),
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $uri = strtok($uri, '?');

        if ($uri === false) {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (!preg_match($route['regex'], $uri, $matches)) {
                continue;
            }

            /** @var array<string, string> $params */
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['middleware'] as $middleware) {
                $middleware();
            }

            ($route['handler'])($params);

            return;
        }

        throw new HttpException('Route not found', 404);
    }
}
