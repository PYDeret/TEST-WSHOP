<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Store;

interface StoreRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return list<Store>
     */
    public function findAll(array $filters, string $sort, string $order, int $page, int $perPage): array;

    /**
     * @param array<string, mixed> $filters
     */
    public function countAll(array $filters): int;

    public function findById(int $id): ?Store;

    public function create(Store $store): Store;

    public function update(Store $store): Store;

    public function delete(int $id): void;
}
