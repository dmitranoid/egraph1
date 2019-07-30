<?php
// Application middleware

use Slim\Middleware\RoutingMiddleware;

$app->add(new RoutingMiddleware($app->getRouteResolver(), $app->getRouteCollector()->getRouteParser());

$app->add(
    new Slim\Middleware\ErrorMiddleware(
        $app->getCallableResolver(),
        $app->getResponseFactory(),
        true,
        true,
        true
    )
);

// e.g: $app->add(new \Slim\Csrf\Guard);
$app->add(new App\Middlewares\SessionMiddleware());
