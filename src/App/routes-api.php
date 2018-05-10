<?php
/**
 * @var Slim\App $app
 */
$app->group('/api/v1/', function() {

    $this->any('test/{method}', App\Api\Controllers\Test\TestController::class);
});