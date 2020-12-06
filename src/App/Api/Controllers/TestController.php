<?php

namespace App\Api\Controllers;

use App\Api\ApiResourceGenericController;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestController extends ApiResourceGenericController
{
    protected $allowedActions = [
      'show',
    ];

    public function show(RequestInterface $request, ResponseInterface $response, array $args)
    {
        return $response->withJson([
            'title' => 'test title',
            'content' => 'test content'
        ]);
    }
}
