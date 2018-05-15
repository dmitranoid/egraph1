<?php
/**
 * @var Slim\App $app
 */

function responseJsonError(\Psr\Http\Message\ResponseInterface $response, $statusCode = 200, $message = [])
{
    return $response->withStatus($statusCode)->withJson($message);
}

$app->group('/api/v1/', function() {

    $ci = $this->getContainer();

    $this->any('{class}/{method}', function ($request, $response, $args) use ($ci) {
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
});