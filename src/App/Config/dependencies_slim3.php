<?php
// -----------------------------------------------------------------------------
// Service providers
// -----------------------------------------------------------------------------

// Twig
$container->set('view', function ($c) {
    $settings = $c->get('settings');
    $view = new Slim\Views\Twig($settings['view']['template_path'], $settings['view']['twig']);

    // Add extensions
    $view->addExtension(new Slim\Views\TwigExtension($c->get('router'), $c->get('request')->getUri()));
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
});

// Flash messages
$container['flash'] = function ($c) {
    return new Slim\Flash\Messages;
};

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------

$container['db'] = function($c) {
    $db = $c->get('settings')['database'];
    $dsn = "{$db['driver']}:{$db['dbname']}";
    return new \Pdo($dsn, $db['user'], $db['password']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['logger']['path'], Monolog\Logger::DEBUG));
    return $logger;
};

$container['repositoryFactory'] = function($c) {
    $db = $c->get('db');
    $repositoryFactory = new App\Infrastructure\Repository\PDORepositoryFactory($db);
    return $repositoryFactory;
};

$container['validatorFactory'] = function($c) {
    $validatorFactory = new App\Validators\ValidatorsFactory();
    return $repositoryFactory;
};

$container['eventDispatcher'] = function($c) {
    $logger = $c->get('logger');
    return new App\Dispatchers\LoggerEventDispatcher($logger);
};


// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------

$container[App\Actions\HomeAction::class] = function ($c) {
    return new App\Actions\HomeAction($c->get('view'), $c->get('logger'));
};
