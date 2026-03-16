<?php

declare(strict_types=1);

$dbPath = __DIR__ . '/booking.db';

if (file_exists($dbPath)) {
    unlink($dbPath);
}

$db = new SQLite3($dbPath);
$db->enableExceptions(true);

$db->exec(file_get_contents(__DIR__ . '/schema.sql'));
$db->exec(file_get_contents(__DIR__ . '/seed.sql'));

echo "Database ready: {$dbPath}\n";

$db->close();
