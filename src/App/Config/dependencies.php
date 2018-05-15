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
    \App\Infrastructure\View\ViewInterface::class => factory(function (ContainerInterface $c) use ($app){
        $settings = $c->get('settings');
        $twig = new Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);

        // Add extensions
        $twig->addExtension(new Slim\Views\TwigExtension($app->getRouter(), ''));
        $twig->addExtension(new Twig_Extension_Debug());

        return new \App\Infrastructure\View\TwigView($twig);
    }),
    'view'=> get(\App\Infrastructure\View\ViewInterface::class),
    'flash' => factory(function (ContainerInterface $c) {
        return new Slim\Flash\Messages;
    }),
    \Psr\Log\LoggerInterface::class => function (ContainerInterface $c) {
        $settings = $c->get('settings');
        $logger = new Monolog\Logger($settings['logger']['name']);
        $logger->pushProcessor(new Monolog\Processor\UidProcessor());
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
        return $logger;
    },
    'logger' => get(\Psr\Log\LoggerInterface::class),
    'db' => factory(function(ContainerInterface $c) {
        $db = $c->get('settings')['database'];
        $dsn = "{$db['driver']}:{$db['dbname']}";
        return new \Pdo($dsn, $db['user'], $db['password']);
    }),
    \PDO::class => get('db'),
    'repositoryFactory' => function(ContainerInterface $c) {
        $db = $c->get('db');
        $repositoryFactory = new App\Infrastructure\Repository\PDORepositoryFactory($db);
        return $repositoryFactory;
    },

    'validatorFactory' => function(ContainerInterface $c) {
        $validatorFactory = new App\Validators\ValidatorFactory();
        return $validatorFactory;
    },

    'eventDispatcher' => factory(function($logger) {
        return new App\Dispatchers\LoggerEventDispatcher($logger);
    })->parameter('logger', get('logger')),


];