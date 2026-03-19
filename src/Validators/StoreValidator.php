<?php

declare(strict_types=1);

namespace App\Validators;

class StoreValidator
{
    /** @var list<string> */
    private const REQUIRED_FIELDS = ['name', 'address', 'city', 'postal_code'];

    /** @var array<string, int> */
    private const MAX_LENGTHS = [
        'name' => 150,
        'address' => 255,
        'city' => 100,
        'postal_code' => 20,
        'country' => 100,
        'phone' => 30,
        'email' => 150,
        'category' => 80,
    ];

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validateCreate(array $data): array
    {
        $errors = [];

        foreach (self::REQUIRED_FIELDS as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "The $field field is required.";
            }
        }

        if (empty($errors)) {
            $errors = array_merge($errors, $this->validateCommon($data));
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validateUpdate(array $data): array
    {
        return $this->validateCommon($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validatePatch(array $data): array
    {
        if (empty($data)) {
            return [
                'body' => 'Request body must not be empty.',
            ];
        }

        return $this->validateCommon($data);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validateCommon(array $data): array
    {
        $errors = [];

        foreach (self::MAX_LENGTHS as $field => $max) {
            if (isset($data[$field]) && mb_strlen((string)$data[$field]) > $max) {
                $errors[$field] = "The $field field must not exceed $max characters.";
            }
        }

        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'The email field must be a valid email address.';
        }

        if (isset($data['is_active']) && !in_array($data['is_active'], [0, 1, true, false, '0', '1'], true)) {
            $errors['is_active'] = 'The is_active field must be a boolean.';
        }

        return $errors;
    }
}
