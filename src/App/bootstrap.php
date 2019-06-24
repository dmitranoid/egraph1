<?php

use Slim\App;
use Slim\Http\Factory\DecoratedResponseFactory;
use Slim\Http\Decorators\ServerRequestDecorator;
use Slim\Middleware\ErrorMiddleware;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

define('APP_DIR', realpath(__DIR__));
define('ROOT_DIR', realpath(__DIR__.'/../..'));

require ROOT_DIR . '/vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(APP_DIR);
$dotenv->overload();

if ('dev' == strtolower(getenv('ENV'))) {
    // asserts
    assert_options(ASSERT_ACTIVE, true);
    assert_options(ASSERT_BAIL, true);
    //assert_options(ASSERT_CALLBACK, null);
    // xdebug
    ini_set('xdebug.var_display_max_depth', 10);
    ini_set('xdebug.var_display_max_children', 256);
    ini_set('xdebug.var_display_max_data', 1024);
} else {
    assert_options(ASSERT_ACTIVE, false);
}

// Slim
$slimSettings = [
    'determineRouteBeforeAppMiddleware' => false,
    'displayErrorDetails' => getenv('DEBUG'),
];

$console = PHP_SAPI == 'cli' ? true : false;

if ($console) {
    set_time_limit(0);
    $argv = $GLOBALS['argv'];
    array_shift($argv);
    $pathInfo = implode('/', $argv);
    //Convert $argv to PATH_INFO
    $env = \Slim\Http\Environment::mock([
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
        'REQUEST_URI' => count($argv) >= 2 ? "/{$argv[0]}/{$argv[1]}" : $pathInfo
    ]);
    $slimSettings['environment'] = $env;
}


$responseFactory = new DecoratedResponseFactory(new ResponseFactory(), new StreamFactory());

// DI
$definitions = require APP_DIR . '/Config/dependencies.php';
$container = (new \DI\ContainerBuilder())
    ->useAnnotations(false)
    ->useAutowiring(true)
    ->addDefinitions($definitions)
    ->build();

$app = new App($responseFactory, $container);
$app->addSettings($slimSettings);

// Debug helpers
require APP_DIR . '/Helpers/DebugFunctions.php';

// Register middleware
require APP_DIR . '/Config/middleware.php';

// Register routes
if ($console) {
    require APP_DIR . '/routes-cli.php';
} else {
    require APP_DIR . '/routes-api.php';
    require APP_DIR . '/routes-web.php';
}

$request = ServerRequestFactory::createFromGlobals();
$serverRequest = new ServerRequestDecorator($request);
$app->run($serverRequest);
