<?php

namespace App\Middlewares;

use App\Services\ApiAuthService;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Слой аутентификации при обращении к API
 */
class ApiAuthModdleware 
{
    protected $container;

    /**
     * @var ApiAuthService
     */
    protected $authService;

    public function __construct($container) {
        $this->container = $container;
    }

    public function __invoke(ServerRequestInterface $request, ResponceInterface $response, $next) {

        $token = ''; //$request->getHeaders('auth_token');

        if (false === $this->authService->checkToken($token)) {

        }
        
        return $next($request, $response);
    }
}