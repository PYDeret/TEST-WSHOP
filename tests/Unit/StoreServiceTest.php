<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Cache\NullCache;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Store;
use App\Repositories\Contracts\StoreRepositoryInterface;
use App\Serializers\StoreSerializer;
use App\Services\StoreService;
use App\Validators\StoreValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreServiceTest extends TestCase
{
    private StoreRepositoryInterface&MockObject $repository;
    private StoreService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(StoreRepositoryInterface::class);

        $this->service = new StoreService(
            $this->repository,
            new StoreValidator(),
            new StoreSerializer(),
            new NullCache(),
        );
    }

    private function makeStore(int $id = 1): Store
    {
        return new Store(
            id: $id,
            name: 'Test Store',
            address: '1 rue Test',
            city: 'Paris',
            postalCode: '75000',
            country: 'FR',
            phone: null,
            email: null,
            category: null,
            isActive: true,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );
    }

    public function test_list_returns_paginated_result(): void
    {
        $stores = [$this->makeStore(1), $this->makeStore(2)];

        $this->repository->expects($this->once())
            ->method('findAll')
            ->willReturn($stores);

        $this->repository->expects($this->once())
            ->method('countAll')
            ->willReturn(2);

        $result = $this->service->list([]);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertCount(2, $result['data']);
        $this->assertSame(2, $result['meta']['total']);
    }

    public function test_show_returns_store(): void
    {
        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->makeStore(1));

        $result = $this->service->show(1);

        $this->assertArrayHasKey('data', $result);
        $this->assertSame(1, $result['data']['id']);
    }

    public function test_show_throws_not_found_when_store_missing(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->show(999);
    }

    public function test_create_with_valid_data_returns_store(): void
    {
        $body = [
            'name' => 'New Store',
            'address' => '1 rue Neuve',
            'city' => 'Lyon',
            'postal_code' => '69000',
        ];
        $saved = $this->makeStore(10);

        $this->repository->expects($this->once())
            ->method('create')
            ->willReturn($saved);

        $result = $this->service->create($body);

        $this->assertSame(10, $result['data']['id']);
    }

    public function test_create_with_invalid_data_throws_validation_exception(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->create([]);
    }

    public function test_delete_throws_not_found_when_store_missing(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->delete(999);
    }

    public function test_delete_calls_repository(): void
    {
        $this->repository->method('findById')->willReturn($this->makeStore(1));

        $this->repository->expects($this->once())
            ->method('delete')
            ->with(1);

        $this->service->delete(1);
    }

    public function test_patch_throws_not_found_when_store_missing(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->patch(999, ['city' => 'Marseille']);
    }

    public function test_patch_merges_partial_data(): void
    {
        $existing = $this->makeStore(1);
        $updated = new Store(
            id: 1,
            name: 'Test Store',
            address: '1 rue Test',
            city: 'Marseille',
            postalCode: '75000',
            country: 'FR',
            phone: null,
            email: null,
            category: null,
            isActive: true,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-02 00:00:00',
        );

        $this->repository->method('findById')->willReturn($existing);
        $this->repository->method('update')->willReturn($updated);

        $result = $this->service->patch(1, ['city' => 'Marseille']);

        $this->assertSame('Marseille', $result['data']['city']);
    }
}
