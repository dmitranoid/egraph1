<?php

namespace App\Middlewares;

use App\Services\ApiAuthService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Слой аутентификации при обращении к API
 */
class ApiAuthMiddleware implements MiddlewareInterface
{
    protected $container;

    /**
     * @var ApiAuthService
     */
    protected $authService;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): RequestHandlerInterface
    {
        $token = ''; //$request->getHeaders('auth_token');

        if (false === $this->authService->checkToken($token)) {
        }

        return $next->handle($request);
    }
}
