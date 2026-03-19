#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$host = $_ENV['DB_HOST'] ?? 'mysql';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'] ?? 'wshop';
$username = $_ENV['DB_USERNAME'] ?? 'wshop';
$password = $_ENV['DB_PASSWORD'] ?? '';

$dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

$migrationDir = __DIR__ . '/../database/migrations';
$files = glob($migrationDir . '/*.sql');

sort($files);

foreach ($files as $file) {
    $sql = file_get_contents($file);
    $pdo->exec($sql);
}

echo 'All migrations applied.' . PHP_EOL;
