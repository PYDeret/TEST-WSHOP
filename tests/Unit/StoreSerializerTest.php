<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Store;
use App\Serializers\StoreSerializer;
use PHPUnit\Framework\TestCase;

class StoreSerializerTest extends TestCase
{
    private StoreSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new StoreSerializer();
    }

    private function makeStore(int $id = 1): Store
    {
        return new Store(
            id: $id,
            name: 'WSHOP',
            address: '11BIS-13, RUE DU COLISÉE',
            city: 'Paris',
            postalCode: '75008',
            country: 'FR',
            phone: '0142460228',
            email: 'contact@wshop.com',
            category: 'clothing',
            isActive: true,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }

    public function test_to_array_returns_all_fields(): void
    {
        $store = $this->makeStore();
        $result = $this->serializer->toArray($store);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('address', $result);
        $this->assertArrayHasKey('city', $result);
        $this->assertArrayHasKey('postal_code', $result);
        $this->assertArrayHasKey('country', $result);
        $this->assertArrayHasKey('is_active', $result);
        $this->assertArrayHasKey('created_at', $result);
        $this->assertArrayHasKey('updated_at', $result);
    }

    public function test_to_array_maps_values_correctly(): void
    {
        $store = $this->makeStore(42);
        $result = $this->serializer->toArray($store);

        $this->assertSame(42, $result['id']);
        $this->assertSame('WSHOP', $result['name']);
        $this->assertSame('75008', $result['postal_code']);
        $this->assertTrue($result['is_active']);
    }

    public function test_collection_returns_array_of_serialized_stores(): void
    {
        $stores = [$this->makeStore(1), $this->makeStore(2)];
        $result = $this->serializer->collection($stores);

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame(2, $result[1]['id']);
    }
}
