<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 28.03.2018
 * Time: 7:52
 */

namespace App\Infrastructure\View;


use Psr\Http\Message\ResponseInterface;

interface ViewInterface
{
    /**
     * @param ResponseInterface $response
     * @param $template
     * @param array $data
     * @return ResponseInterface
     */
    function render(ResponseInterface $response, $template, $data = []):ResponseInterface;
}