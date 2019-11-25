<?php

namespace Tests\App\Includes;

use \PDO;

function initSqliteDb(): PDO
{
    $sqlitePath = realpath(__DIR__ . '/../../../data/');
    if (!file_exists($sqlitePath . '/data.sqlite3')) {
        die($sqlitePath . '/data.sqlite3 not found');
    }
    $pdo = new PDO(
        'sqlite:' . $sqlitePath . '/data.sqlite3',
        '',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec('ATTACH  \'' . $sqlitePath . '/dict-gpo.sqlite3\' as gpo');
    $pdo->exec('PRAGMA journal_mode = MEMORY');
    return $pdo;
}