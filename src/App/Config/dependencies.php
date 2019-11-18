<?php

// php-di configuration

use App\Infrastructure\View\ViewInterface;
use App\ServiceProviders\PDOSQLiteServiceProvider;
use App\ServiceProviders\TwigServiceProvider;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function DI\create;
use function DI\get;
use function DI\factory;


return [
// Settings
    'settings' => require APP_DIR . '/Config/settings.php',
// Service providers
    ViewInterface::class => DI\factory(function (ContainerInterface $c) {
        return TwigServiceProvider::register($c);
    }),
    'view'=> get(ViewInterface::class),
    'flash' => factory(function (ContainerInterface $c) {
        return new Slim\Flash\Messages;
    }),
    LoggerInterface::class => DI\factory(function (ContainerInterface $c) {
        return App\ServiceProviders\MonologServiceProvider::register($c);
    }),
    'logger' => get(LoggerInterface::class),
    \PDO::class => DI\factory(function (ContainerInterface $c) {
        return PDOSQLiteServiceProvider::register($c);
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

    \Slim\Interfaces\RouteParserInterface::class => factory(function (ContainerInterface $c) {

        return app()->getRouteCollector()->getRouteParser();
    })

];
