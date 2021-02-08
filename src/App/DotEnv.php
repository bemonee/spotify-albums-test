<?php

declare(strict_types=1);

$baseDir = __DIR__ . '/../../';

$dotenv = Dotenv\Dotenv::createImmutable($baseDir);

if (file_exists($baseDir . '.env')) {
    $dotenv->load();
}

$dotenv->required([
    'ALBUM_FINDER_SERVICE',
    'ALBUM_FINDER_API_CLIENT_ID',
    'ALBUM_FINDER_API_CLIENT_SECRET',
]);
