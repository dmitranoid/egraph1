<?php
// Routes

/** @var App $app */

use App\Actions\Admin\Energonetwork\Import\Index;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

$app->get('/', function (RequestInterface $request, ResponseInterface $response, $args) use ($app) {
    $response->getBody()->write('homepage');
    return $response;
})->setName('homepage');

$app->group('/admin', function ($app) {
    /** @var App $app */
    $app->get('/', App\Http\Controllers\Admin\Dashboard\Index::class)->setName('adm.dashboard.index');
    $app->get('/network/import', App\Http\Controllers\Admin\Energonetwork\Import\Index::class)->setName('adm.network.import.index');
    $app->post('/network/import/start', App\Http\Controllers\Admin\Energonetwork\Import\DoImport::class)->setName('adm.network.import.doimport');

});

$app->get('/test', '\App\Http\Controllers\Test\TestController:indexAction')->setName('test.index');
$app->get('/test2', '\App\Http\Controllers\Test\TestController:testAction')->setName('test.test');
$app->get('/testd', '\App\Http\Controllers\Test\TestController:testDomainAction')->setName('test.domain');

$app->get('/testdomain', function($request, $response, $args) use ($app) {

    $container = $app->getContainer();
    $validator = new App\Validators\User\UserValidator;
    $router = $app->getRouter();

    $userService = new App\Services\UserService(
        $container->get('repositoryFactory')->getRepository('User'),
        $container->get('eventDispatcher')
    );

    $userData = $request->getQueryParams();
    if(!$validator->validate($userData)) {
        $errors = $validator->getErrors();
        return $response;    
    }

    $userService->create($userData['name'] ?? 'null');
    return $response;
});
    
