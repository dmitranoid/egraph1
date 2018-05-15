<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 02.04.2018
 * Time: 11:02
 */

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
            'title'=>'test title',
            'content'=>'test content'
        ]);
    }


}