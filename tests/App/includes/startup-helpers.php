<?php

function initSqliteDb(): PDO
{
    $sqlitePath = '../../../data/';
//    echo(realpath(__DIR__.'/'.$sqlitePath));
    $pdo = new PDO(
        'sqlite:' . $sqlitePath . 'data.sqlite3',
        '',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec('ATTACH  \'' . $sqlitePath . 'dict-gpo.sqlite3\' as gpo');
    $pdo->exec('PRAGMA journal_mode = MEMORY');
    return $pdo;
}