<?php

declare(strict_types=1);

namespace App\Models;

readonly class Store
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $address,
        public string $city,
        public string $postalCode,
        public string $country,
        public ?string $phone,
        public ?string $email,
        public ?string $category,
        public bool $isActive,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int)$data['id'] : null,
            name: (string)$data['name'],
            address: (string)$data['address'],
            city: (string)$data['city'],
            postalCode: (string)$data['postal_code'],
            country: isset($data['country']) ? (string)$data['country'] : 'FR',
            phone: isset($data['phone']) ? (string)$data['phone'] : null,
            email: isset($data['email']) ? (string)$data['email'] : null,
            category: isset($data['category']) ? (string)$data['category'] : null,
            isActive: !isset($data['is_active']) || $data['is_active'],
            createdAt: isset($data['created_at']) ? (string)$data['created_at'] : null,
            updatedAt: isset($data['updated_at']) ? (string)$data['updated_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'category' => $this->category,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
