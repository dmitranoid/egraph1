<?php

namespace App\ServiceProviders;


use Psr\Container\ContainerInterface;

class PDOSQLiteServiceProvider implements ServiceProviderInterface
{
    public static function register(ContainerInterface $container)
    {
        $db = $container->get('settings')['database'];
        $dsn = "{$db['driver']}:{$db['dbname']}";
        return new \PDO($dsn, $db['user'], $db['password']);
    }

}