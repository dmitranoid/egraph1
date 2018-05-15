<?php
/**
 * @var Slim\App $app
 */
$app->group('/', function () {
    $this->get('test', '\App\Http\Controllers\Test\TestController:indexAction');
});