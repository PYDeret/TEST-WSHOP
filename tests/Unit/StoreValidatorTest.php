<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validators\StoreValidator;
use PHPUnit\Framework\TestCase;

class StoreValidatorTest extends TestCase
{
    private StoreValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new StoreValidator();
    }

    public function test_create_valid_data_returns_no_errors(): void
    {
        $data = [
            'name' => 'Ma Boutique',
            'address' => '10 rue de la Paix',
            'city' => 'Paris',
            'postal_code' => '75001',
        ];

        $this->assertEmpty($this->validator->validateCreate($data));
    }

    public function test_create_missing_required_fields_returns_errors(): void
    {
        $errors = $this->validator->validateCreate([]);

        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('address', $errors);
        $this->assertArrayHasKey('city', $errors);
        $this->assertArrayHasKey('postal_code', $errors);
    }

    public function test_create_invalid_email_returns_error(): void
    {
        $data = [
            'name' => 'Shop',
            'address' => '1 rue',
            'city' => 'Lyon',
            'postal_code' => '69000',
            'email' => 'not-an-email',
        ];

        $errors = $this->validator->validateCreate($data);

        $this->assertArrayHasKey('email', $errors);
    }

    public function test_create_valid_email_passes(): void
    {
        $data = [
            'name' => 'Shop',
            'address' => '1 rue',
            'city' => 'Lyon',
            'postal_code' => '69000',
            'email' => 'contact@shop.fr',
        ];

        $this->assertArrayNotHasKey('email', $this->validator->validateCreate($data));
    }

    public function test_create_name_too_long_returns_error(): void
    {
        $data = [
            'name' => str_repeat('a', 151),
            'address' => '1 rue',
            'city' => 'Lyon',
            'postal_code' => '69000',
        ];

        $errors = $this->validator->validateCreate($data);

        $this->assertArrayHasKey('name', $errors);
    }

    public function test_patch_empty_body_returns_error(): void
    {
        $errors = $this->validator->validatePatch([]);

        $this->assertNotEmpty($errors);
    }

    public function test_patch_valid_partial_data_returns_no_errors(): void
    {
        $errors = $this->validator->validatePatch(['city' => 'Marseille']);

        $this->assertEmpty($errors);
    }
}
