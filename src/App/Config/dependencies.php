<?php

// php-di configuration

use Psr\Container\ContainerInterface;
use function DI\create;
use function DI\get;
use function DI\factory;

return [
// Settings
    'settings' => require APP_DIR . '/Config/settings.php',
// Service providers
    \App\Infrastructure\View\ViewInterface::class => DI\factory(function (ContainerInterface $c) {
        return \App\ServiceProviders\TwigServiceProvider::register($c);
    }),
    'view'=> get(\App\Infrastructure\View\ViewInterface::class),
    'flash' => factory(function (ContainerInterface $c) {
        return new Slim\Flash\Messages;
    }),
    \Psr\Log\LoggerInterface::class => DI\factory(function (ContainerInterface $c) {
        return App\ServiceProviders\MonologServiceProvider::register($c);
    }),
    'logger' => get(\Psr\Log\LoggerInterface::class),
    \PDO::class => DI\factory(function (ContainerInterface $c) {
        return \App\ServiceProviders\PDOSQLiteServiceProvider::register($c);
    }),
    'db' => get(\PDO::class),
// factories
    'repositoryFactory' => function (ContainerInterface $c) {
        $db = $c->get(\PDO::class);
        $repositoryFactory = new App\Infrastructure\Repository\PDORepositoryFactory($db);
        return $repositoryFactory;
    },

    'validatorFactory' => function (ContainerInterface $c) {
        $validatorFactory = new App\Validators\ValidatorFactory();
        return $validatorFactory;
    },

    'eventDispatcher' => factory(function ($logger) {
        return new App\Dispatchers\LoggerEventDispatcher($logger);
    })->parameter('logger', get('logger')),

    \App\Http\Controllers\Test\TestController::class => DI\factory(function (ContainerInterface $c) {
        return new App\Http\Controllers\Test\TestController($c->get('view'), $c->get('logger'));
    }),

];
