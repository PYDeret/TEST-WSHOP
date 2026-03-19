<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\HttpException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;

class AuthService
{
    private string $jwtSecret;
    private int $jwtTtl;

    public function __construct(private readonly PDO $pdo)
    {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? '';
        $this->jwtTtl = (int)($_ENV['JWT_TTL'] ?? 86400);

        if (empty($this->jwtSecret)) {
            throw new \RuntimeException('JWT_SECRET must be configured');
        }
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function register(array $body): array
    {
        $errors = $this->validateCredentials($body);

        if ($errors) {
            throw new ValidationException($errors);
        }

        $email = mb_strtolower(trim((string)$body['email']));
        $password = (string)$body['password'];

        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([
            ':email' => $email,
        ]);

        if ($stmt->fetch()) {
            throw new HttpException('Email already registered', 409);
        }

        $stmt = $this->pdo->prepare('INSERT INTO users (email, password) VALUES (:email, :password)');
        $stmt->execute([
            ':email' => $email,
            ':password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);

        $userId = (int)$this->pdo->lastInsertId();
        $token = $this->generateToken($userId, $email);

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $this->jwtTtl,
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function login(array $body): array
    {
        $errors = $this->validateCredentials($body);

        if ($errors) {
            throw new ValidationException($errors);
        }

        $email = mb_strtolower(trim((string)$body['email']));
        $password = (string)$body['password'];

        $stmt = $this->pdo->prepare('SELECT id, password FROM users WHERE email = :email');
        $stmt->execute([
            ':email' => $email,
        ]);

        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            throw new UnauthorizedException('Invalid credentials');
        }

        $token = $this->generateToken((int)$user['id'], $email);

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $this->jwtTtl,
        ];
    }

    public function verifyToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
        } catch (\Throwable) {
            throw new UnauthorizedException('Invalid or expired token');
        }
    }

    private function generateToken(int $userId, string $email): string
    {
        $payload = [
            'iss' => 'wshop-api',
            'sub' => $userId,
            'email' => $email,
            'iat' => time(),
            'exp' => time() + $this->jwtTtl,
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, string>
     */
    private function validateCredentials(array $body): array
    {
        $errors = [];

        if (empty($body['email'])) {
            $errors['email'] = 'The email field is required.';
        } elseif (!filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'The email field must be a valid email address.';
        }

        if (empty($body['password'])) {
            $errors['password'] = 'The password field is required.';
        } elseif (strlen((string)$body['password']) < 8) {
            $errors['password'] = 'The password must be at least 8 characters.';
        }

        return $errors;
    }
}
