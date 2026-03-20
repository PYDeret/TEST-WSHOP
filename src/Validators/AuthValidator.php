<?php

declare(strict_types=1);

namespace App\Validators;

class AuthValidator
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    public function validateCreate(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'The email field is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'The email field must be a valid email address.';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'The password field is required.';
        } elseif (strlen((string)$data['password']) < 8) {
            $errors['password'] = 'The password must be at least 8 characters.';
        }

        return $errors;
    }
}
