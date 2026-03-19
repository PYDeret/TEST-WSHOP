<?php

declare(strict_types=1);

use App\Bootstrap\Container;
use App\Bootstrap\Routes;
use App\Exceptions\HttpException;
use App\Http\Response;
use App\Router\Router;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

header('Content-Type: application/json; charset=UTF-8');

$container = new Container();
$router = new Router();

Routes::register($container, $router);

try {
    $router->dispatch(
        $_SERVER['REQUEST_METHOD'],
        $_SERVER['REQUEST_URI'],
    );
} catch (HttpException $e) {
    $body = ['error' => $e->getMessage()];

    if ($e->getErrors()) {
        $body['errors'] = $e->getErrors();
    }

    Response::json($body, $e->getStatusCode());
} catch (\Throwable $e) {
    $debug = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
    $message = $debug ? $e->getMessage() : 'Internal server error';

    Response::json(['error' => $message], 500);
}
