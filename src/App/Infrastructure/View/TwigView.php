<?php

namespace App\Infrastructure\View;

use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;
use Twig\Environment;

class TwigView implements ViewInterface
{
    private $twig;

    /**
     * TwigView constructor.
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    function render(ResponseInterface $response, $template, $data = []): ResponseInterface
    {
        $response->getBody()->write($this->twig->render($template, $data));
        return $response;
    }

}
