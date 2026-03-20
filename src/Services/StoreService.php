<?php

declare(strict_types=1);

namespace App\Services;

use App\Cache\CacheInterface;
use App\Exceptions\Store\StoreNotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Store;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Serializers\StoreSerializer;
use App\Validators\StoreValidator;

class StoreService
{
    private const CACHE_TTL_LIST = 60;
    private const CACHE_TTL_SINGLE = 300;

    public function __construct(
        private readonly StoreRepositoryInterface $repository,
        private readonly StoreValidator $validator,
        private readonly StoreSerializer $serializer,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @param array<string, mixed> $queryParams
     * @return array<string, mixed>
     */
    public function list(array $queryParams): array
    {
        $filters = $this->extractFilters($queryParams);
        $sort = isset($queryParams['sort']) ? (string)$queryParams['sort'] : 'id';
        $order = isset($queryParams['order']) ? (string)$queryParams['order'] : 'asc';
        $page = max(1, (int)($queryParams['page'] ?? 1));
        $perPage = min(100, max(1, (int)($queryParams['per_page'] ?? 20)));

        $cacheKey = 'stores:list:' . md5(serialize([$filters, $sort, $order, $page, $perPage]));
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            /** @var array<string, mixed> $cached */
            return $cached;
        }

        $stores = $this->repository->findAll($filters, $sort, $order, $page, $perPage);
        $total = $this->repository->countAll($filters);

        $result = [
            'data' => $this->serializer->collection($stores),
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'pages' => (int)ceil($total / $perPage),
            ],
        ];

        $this->cache->set($cacheKey, $result, self::CACHE_TTL_LIST);

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function show(int $id): array
    {
        $cacheKey = "stores:$id";
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            /** @var array<string, mixed> $cached */
            return $cached;
        }

        $store = $this->repository->findById($id);

        if ($store === null) {
            throw new StoreNotFoundException($id);
        }

        $result = ['data' => $this->serializer->toArray($store)];
        $this->cache->set($cacheKey, $result, self::CACHE_TTL_SINGLE);

        return $result;
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function create(array $body): array
    {
        $errors = $this->validator->validateCreate($body);

        if ($errors) {
            throw new ValidationException($errors);
        }

        $store = Store::fromArray($body);
        $saved = $this->repository->create($store);

        $this->cache->deleteByPattern('stores:list:*');

        return [
            'data' => $this->serializer->toArray($saved),
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function update(int $id, array $body): array
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new StoreNotFoundException($id);
        }

        $errors = $this->validator->validateUpdate($body);

        if ($errors) {
            throw new ValidationException($errors);
        }

        $merged = array_merge($existing->toArray(), $body);
        $store = Store::fromArray([...$merged, 'id' => $id]);
        $saved = $this->repository->update($store);

        $this->invalidateSingle($id);

        return [
            'data' => $this->serializer->toArray($saved),
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function patch(int $id, array $body): array
    {
        $existing = $this->repository->findById($id);

        if ($existing === null) {
            throw new StoreNotFoundException($id);
        }

        $errors = $this->validator->validatePatch($body);

        if ($errors) {
            throw new ValidationException($errors);
        }

        $merged = array_merge($existing->toArray(), $body);
        $store = Store::fromArray([...$merged, 'id' => $id]);
        $saved = $this->repository->update($store);

        $this->invalidateSingle($id);

        return [
            'data' => $this->serializer->toArray($saved),
        ];
    }

    public function delete(int $id): void
    {
        if ($this->repository->findById($id) === null) {
            throw new StoreNotFoundException($id);
        }

        $this->repository->delete($id);
        $this->invalidateSingle($id);
    }

    private function invalidateSingle(int $id): void
    {
        $this->cache->delete("stores:$id");
        $this->cache->deleteByPattern('stores:list:*');
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private function extractFilters(array $params): array
    {
        $allowed = [
            'city',
            'country',
            'category',
            'is_active',
            'name',
        ];

        $filters = [];

        foreach ($allowed as $key) {
            if (isset($params[$key]) && $params[$key] !== '') {
                $filters[$key] = $params[$key];
            }
        }

        return $filters;
    }
}
