<?php

namespace App\ServiceProviders;


use PDO;
use Psr\Container\ContainerInterface;

class PDOSQLiteServiceProvider implements ServiceProviderInterface
{
    public static function register(ContainerInterface $container)
    {
        $db = $container->get('settings')['database'];
        $dsn = "{$db['driver']}:{$db['dbname']}";
        $pdo = new PDO($dsn, $db['user'], $db['password']);
        $pdo->exec('PRAGMA journal_mode = MEMORY');
        return $pdo;
    }

}