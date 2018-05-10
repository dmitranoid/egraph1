<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 02.04.2018
 * Time: 8:02
 */

namespace App\Api;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ApiResourceControllerInterface
{

    public function index(RequestInterface $request, ResponseInterface $response, array $args);

    public function show(RequestInterface $request, ResponseInterface $response, array $args);

    public function store(RequestInterface $request, ResponseInterface $response, array $args);
}