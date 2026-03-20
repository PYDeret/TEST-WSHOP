<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Auth\EmailAlreadyExistingException;
use App\Exceptions\Auth\ExpiredTokenException;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\JwtNotConfiguredException;
use App\Exceptions\ValidationException;
use App\Validators\AuthValidator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;

class AuthService
{
    private const ALGORITMH = 'HS256';
    private const TOKEN_TYPE = 'Bearer';

    private string $jwtSecret;
    private int $jwtTtl;

    public function __construct(
        private readonly PDO $pdo,
        private readonly AuthValidator $authValidator,
    ) {
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? '';
        $this->jwtTtl = (int)($_ENV['JWT_TTL'] ?? 86400);

        if (empty($this->jwtSecret)) {
            throw new JwtNotConfiguredException();
        }
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function register(array $body): array
    {
        $errors = $this->authValidator->validateCreate($body);

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
            throw new EmailAlreadyExistingException();
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
            'token_type' => self::TOKEN_TYPE,
            'expires_in' => $this->jwtTtl,
        ];
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function login(array $body): array
    {
        $errors = $this->authValidator->validateCreate($body);

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
            throw new InvalidCredentialsException();
        }

        $token = $this->generateToken((int)$user['id'], $email);

        return [
            'token' => $token,
            'token_type' => self::TOKEN_TYPE,
            'expires_in' => $this->jwtTtl,
        ];
    }

    public function verifyToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->jwtSecret, self::ALGORITMH));
        } catch (\Throwable) {
            throw new ExpiredTokenException();
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

        return JWT::encode($payload, $this->jwtSecret, self::ALGORITMH);
    }
}
