<?php

use Slim\App;
use App\Middlewares\CORSMiddleware;

function responseJsonError(Psr\Http\Message\ResponseInterface $response, $statusCode = 200, $message = [])
{
    return $response->withStatus($statusCode)->withJson($message);
}

$app->group('/api/v1/', function ($app) {
    /** @var App $app */
    $ci = $app->getContainer();

    $app->group('network/', function ($app) {
        /** @var App $app */
        $app->get('mesh', 'App\Api\Controllers\EnergoMesh\EnergoMeshController:all');
        $app->get('mesh/high', 'App\Api\Controllers\EnergoMesh\EnergoMeshController:highNetworks');
        $app->get('mesh/low', 'App\Api\Controllers\EnergoMesh\EnergoMeshController:lowNetworks');
        $app->get('tree', 'App\Api\Controllers\EnergoMesh\EnergoMeshController:all');
        $app->get('tree/vl', 'App\Api\Controllers\EnergoMesh\EnergoMeshController:vl');
        $app->get('tree/rs', 'App\Api\Controllers\EnergoMesh\EnergoMeshController:rs');
    });

    $app->any('{class}/{method}', function ($request, $response, $args) use ($ci) {
        // api classes resolver
        $namespace = 'App\Api\Controllers\\';
        $class = new ReflectionClass($namespace . $args['class'] . 'Controller');

        if (!$class->isInstantiable()) {
            // throw new ReflectionException();
            $errorMessage = [
                'status' => 'error',
                'message' => sprintf('%s not found', $namespace . $args['class']),
            ];
            return responseJsonError($response, 404, $errorMessage);
        }

        $className = $class->getName();
        $response = call_user_func($ci->get($className), $request, $response, $args);
        return $response;
    });
})->add(new CORSMiddleware());
