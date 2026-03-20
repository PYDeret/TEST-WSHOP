<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Cache\NullCache;
use App\Database\Database;
use App\Repositories\StoreRepository;
use App\Serializers\StoreSerializer;
use App\Services\StoreService;
use App\Validators\StoreValidator;
use PDO;
use PHPUnit\Framework\TestCase;

class StoreApiTest extends TestCase
{
    private PDO $pdo;
    private StoreService $service;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $this->pdo->exec((string) file_get_contents(__DIR__ . '/../fixtures/stores_schema.sql'));

        Database::setInstance($this->pdo);

        $this->service = new StoreService(
            new StoreRepository($this->pdo),
            new StoreValidator(),
            new StoreSerializer(),
            new NullCache(),
        );
    }

    protected function tearDown(): void
    {
        Database::reset();
    }

    public function test_list_returns_empty_collection(): void
    {
        $result = $this->service->list([]);

        $this->assertSame([], $result['data']);
        $this->assertSame(0, $result['meta']['total']);
    }

    public function test_create_and_retrieve_store(): void
    {
        $body = [
            'name' => 'Boutique Test',
            'address' => '5 avenue Victor Hugo',
            'city' => 'Paris',
            'postal_code' => '75016',
            'country' => 'FR',
            'category' => 'clothing',
        ];

        $created = $this->service->create($body);

        $this->assertSame('Boutique Test', $created['data']['name']);
        $this->assertSame('Paris', $created['data']['city']);
        $this->assertNotNull($created['data']['id']);

        $fetched = $this->service->show($created['data']['id']);
        $this->assertSame($created['data']['id'], $fetched['data']['id']);
    }

    public function test_list_filters_by_city(): void
    {
        $this->createStore(['city' => 'Paris']);
        $this->createStore(['city' => 'Lyon']);
        $this->createStore(['city' => 'Paris']);

        $result = $this->service->list(['city' => 'Paris']);

        $this->assertCount(2, $result['data']);
        $this->assertSame(2, $result['meta']['total']);
    }

    public function test_list_sorts_by_name(): void
    {
        $this->createStore(['name' => 'Zara']);
        $this->createStore(['name' => 'Adidas']);
        $this->createStore(['name' => 'Nike']);

        $result = $this->service->list(['sort' => 'name', 'order' => 'asc']);
        $names = array_column($result['data'], 'name');

        $this->assertSame(['Adidas', 'Nike', 'Zara'], $names);
    }

    public function test_update_store(): void
    {
        $created = $this->createStore(['name' => 'Old Name']);
        $id = $created['data']['id'];

        $updated = $this->service->update($id, [
            'name' => 'New Name',
            'address' => '1 rue Neuve',
            'city' => 'Bordeaux',
            'postal_code' => '33000',
        ]);

        $this->assertSame('New Name', $updated['data']['name']);
        $this->assertSame('Bordeaux', $updated['data']['city']);
    }

    public function test_patch_store(): void
    {
        $created = $this->createStore(['city' => 'Paris']);
        $id = $created['data']['id'];

        $patched = $this->service->patch($id, ['city' => 'Nantes']);

        $this->assertSame('Nantes', $patched['data']['city']);
    }

    public function test_delete_store(): void
    {
        $created = $this->createStore();
        $id = $created['data']['id'];

        $this->service->delete($id);

        $result = $this->service->list([]);
        $this->assertSame(0, $result['meta']['total']);
    }

    public function test_create_with_missing_required_fields_throws_validation_exception(): void
    {
        $this->expectException(\App\Exceptions\ValidationException::class);

        $this->service->create(['name' => 'Only Name']);
    }

    public function test_show_nonexistent_store_throws_not_found(): void
    {
        $this->expectException(\App\Exceptions\Store\StoreNotFoundException::class);

        $this->service->show(9999);
    }

    public function test_list_pagination(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->createStore(['name' => "Store {$i}"]);
        }

        $page1 = $this->service->list(['page' => '1', 'per_page' => '2']);
        $page2 = $this->service->list(['page' => '2', 'per_page' => '2']);

        $this->assertCount(2, $page1['data']);
        $this->assertCount(2, $page2['data']);
        $this->assertSame(5, $page1['meta']['total']);
        $this->assertSame(3, $page1['meta']['pages']);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function createStore(array $overrides = []): array
    {
        return $this->service->create(array_merge([
            'name' => 'Default Store',
            'address' => '1 rue Principale',
            'city' => 'Paris',
            'postal_code' => '75001',
        ], $overrides));
    }
}
