<?php


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
    public function render(ResponseInterface $response, $template, $data = []):ResponseInterface;
}