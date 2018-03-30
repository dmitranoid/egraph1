<?php

namespace App\Middlewares;

use App\Services\ApiAuthService;

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

    public function __invoke(RequestInterface $request, ResponceInterface $response, $next) {

        $token = ''; //$request->getHeaders('auth_token');

        if (false === $this->authService->checkToken($token)) {

        }
        
        return $next($request, $response);
    }
}