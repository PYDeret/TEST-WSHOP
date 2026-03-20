<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\SqlLoader;
use App\Models\Store;
use App\Repositories\Contracts\StoreRepositoryInterface;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class StoreRepository implements StoreRepositoryInterface
{
    /** @var list<string> */
    private const ALLOWED_SORT_COLUMNS = ['id', 'name', 'city', 'country', 'category', 'is_active', 'created_at', 'updated_at'];

    /** @var list<string> */
    private const ALLOWED_FILTERS = ['city', 'country', 'category', 'is_active'];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<Store>
     */
    public function findAll(array $filters, string $sort, string $order, int $page, int $perPage): array
    {
        [$where, $bindings] = $this->buildWhereClause($filters);

        $sort = in_array($sort, self::ALLOWED_SORT_COLUMNS, true) ? $sort : 'id';
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;

        $sql = str_replace(
            ['{where}', '{sort}', '{order}'],
            [$where, $sort, $order],
            SqlLoader::load('stores/find_all.sql'),
        );

        $stmt = $this->pdo->prepare($sql);

        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_values(array_map(
            static fn (array $row) => Store::fromArray($row),
            $stmt->fetchAll(),
        ));
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function countAll(array $filters): int
    {
        [$where, $bindings] = $this->buildWhereClause($filters);

        $sql = str_replace('{where}', $where, SqlLoader::load('stores/count_all.sql'));
        $stmt = $this->pdo->prepare($sql);

        foreach ($bindings as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): ?Store
    {
        $stmt = $this->pdo->prepare(SqlLoader::load('stores/find_by_id.sql'));
        $stmt->execute([':id' => $id]);

        $row = $stmt->fetch();

        return $row ? Store::fromArray($row) : null;
    }

    public function create(Store $store): Store
    {
        $stmt = $this->pdo->prepare(SqlLoader::load('stores/create.sql'));
        $stmt->execute($this->bindStoreParams($store));

        $created = $this->findById((int)$this->pdo->lastInsertId());

        if ($created === null) {
            throw new RuntimeException('Failed to retrieve store after creation');
        }

        return $created;
    }

    public function update(Store $store): Store
    {
        if ($store->id === null) {
            throw new InvalidArgumentException('Cannot update a store without an id');
        }

        $stmt = $this->pdo->prepare(SqlLoader::load('stores/update.sql'));
        $stmt->execute([...$this->bindStoreParams($store), ':id' => $store->id]);

        $updated = $this->findById($store->id);

        if ($updated === null) {
            throw new RuntimeException('Failed to retrieve store after update');
        }

        return $updated;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare(SqlLoader::load('stores/delete.sql'));
        $stmt->execute([':id' => $id]);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{string, array<string, mixed>}
     */
    private function buildWhereClause(array $filters): array
    {
        $conditions = [];
        $bindings = [];

        foreach ($filters as $field => $value) {
            if ($field === 'name') {
                $conditions[] = 'name LIKE :name';
                $bindings[':name'] = '%' . $value . '%';
                continue;
            }

            if (in_array($field, self::ALLOWED_FILTERS, true)) {
                $conditions[] = "$field = :$field";
                $bindings[":$field"] = $value;
            }
        }

        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

        return [
            $where,
            $bindings,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bindStoreParams(Store $store): array
    {
        return [
            ':name' => $store->name,
            ':address' => $store->address,
            ':city' => $store->city,
            ':postal_code' => $store->postalCode,
            ':country' => $store->country,
            ':phone' => $store->phone,
            ':email' => $store->email,
            ':category' => $store->category,
            ':is_active' => (int)$store->isActive,
        ];
    }
}
