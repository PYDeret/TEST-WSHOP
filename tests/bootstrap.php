<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Set minimal env for tests
$_ENV['JWT_SECRET'] = 'test_secret_key_at_least_32_chars_long';
$_ENV['JWT_TTL'] = '3600';
$_ENV['APP_DEBUG'] = 'true';
