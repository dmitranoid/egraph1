<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 28.03.2018
 * Time: 8:06
 */

namespace App\Infrastructure\View;


use App\Infrastructure\View\ViewInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Views\Twig;

class TwigView implements ViewInterface
{
    private $twig;

    /**
     * TwigView constructor.
     * @param Twig $twig
     */
    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    function render(ResponseInterface $response, $template, $data = []): ResponseInterface
    {
        return $this->twig->render($response, $template, $data);
    }


}