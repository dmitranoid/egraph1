<?php

namespace App\ServiceProviders;


use PDO;
use Psr\Container\ContainerInterface;

class PDOSQLiteServiceProvider implements ServiceProviderInterface
{
    public static function register(ContainerInterface $container)
    {
//        $db = $container->get('settings')['database'];
//        $dsn = "{$db['driver']}:{$db['dbname']}";
//        $pdo = new \PDO($dsn, $db['user'], $db['password']);
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
        $pdo->exec('ATTACH  \'' . $sqlitePath . '/config.sqlite3\' as config');

        $pdo->exec('PRAGMA journal_mode = MEMORY');
        return $pdo;
    }

}