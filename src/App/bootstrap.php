<?php

use DI\ContainerBuilder;
use Slim\App;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;
use Slim\Http\Environment;
use Slim\Interfaces\RouteParserInterface;

define('APP_DIR', realpath(__DIR__));
define('ROOT_DIR', realpath(__DIR__.'/../..'));
define('SERVER_ROOT_DIR', ROOT_DIR);


require ROOT_DIR . '/vendor/autoload.php';

$dotenv = new Dotenv(SERVER_ROOT_DIR);
$dotenv->overload();
$env = strtolower(getenv('mode'));
$dotenv = new Dotenv(SERVER_ROOT_DIR, $env . '.env');
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

$console = PHP_SAPI == 'cli' ? true : false;

if ($console) {
    set_time_limit(0);
    $argv = $GLOBALS['argv'];
    array_shift($argv);
    $pathInfo = implode('/', $argv);
    //Convert $argv to PATH_INFO
    $env = Environment::mock(
        [
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
        'REQUEST_URI' => count($argv) >= 2 ? "/{$argv[0]}/{$argv[1]}" : $pathInfo
        ]
    );
}

 // DI
$definitions = require APP_DIR . '/Config/dependencies.php';
$container = (new ContainerBuilder())
    ->useAnnotations(false)
    ->useAutowiring(true)
    ->addDefinitions($definitions)
    ->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

// костыль для передачи роутинга в контроллеры,
// вынесено сюда, т.к. нет доступа к $app во время создания контейнера
//$container->set(RouteParserInterface::class, $app->getRouteCollector()->getRouteParser());

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

$app->run();
