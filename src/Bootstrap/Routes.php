<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Router\Router;

class Routes
{
    public static function register(Container $container, Router $router): void
    {
        self::addAuthEndpoints($router, $container);
        self::addStoreEndpoints($router, $container);
    }

    /**
     * Public endpoints
     */
    private static function addAuthEndpoints(Router $router, Container $container): void
    {
        $authController = $container->authController();
        $router->add('POST', '/api/auth/register', fn (array $params) => $authController->register());
        $router->add('POST', '/api/auth/login', fn (array $params) => $authController->login());
    }

    /**
     * Protected endpoints
     */
    private static function addStoreEndpoints(Router $router, Container $container): void
    {
        $storeController = $container->storeController();
        $authMiddleware = $container->authMiddleware();
        $router->add('GET', '/api/stores', fn (array $params) => $storeController->findAll(), [$authMiddleware]);
        $router->add('GET', '/api/stores/{id}', fn (array $params) => $storeController->findById($params), [$authMiddleware]);
        $router->add('POST', '/api/stores', fn (array $params) => $storeController->post(), [$authMiddleware]);
        $router->add('PUT', '/api/stores/{id}', fn (array $params) => $storeController->put($params), [$authMiddleware]);
        $router->add('PATCH', '/api/stores/{id}', fn (array $params) => $storeController->patch($params), [$authMiddleware]);
        $router->add('DELETE', '/api/stores/{id}', fn (array $params) => $storeController->delete($params), [$authMiddleware]);
    }
}
