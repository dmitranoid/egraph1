<?php
// Routes
/*
$app->get('/', App\Actions\HomeAction::class)
    ->setName('homepage');
*/
$app->get('/', function(\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) use($app) {
    $response->getBody()->write('homepage');
    return $response;
})->setName('homepage');

$app->get('/test', '\App\Http\Controllers\Test\TestController:indexAction')->setName('test.index');
$app->get('/test2', '\App\Http\Controllers\Test\TestController:testAction')->setName('test.test');

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

    $userService->create($userData['name']?:'null');

});
    
