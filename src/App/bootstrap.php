<?php 

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
// session
session_start();

// Slim
$slimSettings = [
    'determineRouteBeforeAppMiddleware' => false,
    'displayErrorDetails' => getenv('DEBUG'),
];
$app = new \Slim\App($slimSettings);

// DI
$definitions = require APP_DIR . '/Config/dependencies.php';
$container = (new \DI\ContainerBuilder())
    ->useAnnotations(false)
    ->useAutowiring(true)
    ->addDefinitions($definitions)
    ->build();

$app->setContainer($container);

// debug helpers
require APP_DIR . '/Helpers/DebugFunctions.php';

// Register middleware
require APP_DIR . '/Config/middleware.php';

// Register routes
require APP_DIR . '/routes.php';

// initial db settings
if('sqlite' == $container->get('settings')['database']['driver']){
    $container->get('db')->exec('PRAGMA journal_mode=MEMORY;');
    $container->get('db')->exec('PRAGMA busy_timeout=2000;');      
}

// Run!
$app->run();