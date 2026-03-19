<?php

declare(strict_types=1);

namespace App\Serializers;

use App\Models\Store;

class StoreSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Store $store): array
    {
        return [
            'id' => $store->id,
            'name' => $store->name,
            'address' => $store->address,
            'city' => $store->city,
            'postal_code' => $store->postalCode,
            'country' => $store->country,
            'phone' => $store->phone,
            'email' => $store->email,
            'category' => $store->category,
            'is_active' => $store->isActive,
            'created_at' => $store->createdAt,
            'updated_at' => $store->updatedAt,
        ];
    }

    /**
     * @param list<Store> $stores
     * @return list<array<string, mixed>>
     */
    public function collection(array $stores): array
    {
        return array_values(array_map(fn (Store $store) => $this->toArray($store), $stores));
    }
}
