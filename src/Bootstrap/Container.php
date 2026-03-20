<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Cache\CacheInterface;
use App\Cache\NullCache;
use App\Cache\RedisCache;
use App\Controllers\AuthController;
use App\Controllers\StoreController;
use App\Database\Database;
use App\Middleware\AuthMiddleware;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Repositories\StoreRepository;
use App\Serializers\StoreSerializer;
use App\Services\AuthService;
use App\Services\StoreService;
use App\Validators\AuthValidator;
use App\Validators\StoreValidator;
use PDO;

class Container
{
    private ?PDO $pdo = null;

    private ?CacheInterface $cache = null;

    private ?StoreRepositoryInterface $storeRepository = null;

    private ?StoreService $storeService = null;

    private ?AuthService $authService = null;

    private ?StoreController $storeController = null;

    private ?AuthController $authController = null;

    private ?AuthMiddleware $authMiddleware = null;

    public function pdo(): PDO
    {
        return $this->pdo ??= Database::getInstance();
    }

    public function cache(): CacheInterface
    {
        if ($this->cache === null) {
            try {
                $this->cache = new RedisCache();
            } catch (\Throwable) {
                $this->cache = new NullCache();
            }
        }

        return $this->cache;
    }

    public function storeRepository(): StoreRepositoryInterface
    {
        return $this->storeRepository ??= new StoreRepository($this->pdo());
    }

    public function storeService(): StoreService
    {
        return $this->storeService ??= new StoreService(
            $this->storeRepository(),
            new StoreValidator(),
            new StoreSerializer(),
            $this->cache(),
        );
    }

    public function authService(): AuthService
    {
        return $this->authService ??= new AuthService(
            $this->pdo(),
            new AuthValidator(),
        );
    }

    public function storeController(): StoreController
    {
        return $this->storeController ??= new StoreController($this->storeService());
    }

    public function authController(): AuthController
    {
        return $this->authController ??= new AuthController($this->authService());
    }

    public function authMiddleware(): AuthMiddleware
    {
        return $this->authMiddleware ??= new AuthMiddleware($this->authService());
    }
}
