<?php
/**
 * Created by PhpStorm.
 * User: svt3
 * Date: 27.03.2018
 * Time: 11:53
 */

namespace App\Http\Controllers\Test;


use App\Infrastructure\View\ViewInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class TestController
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ViewInterface
     */
    private $view;

    public function __construct(ViewInterface $view, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->view = $view;
        $this->logger->info(__CLASS__.':'.__METHOD__);
    }

    public function indexAction(RequestInterface $request, ResponseInterface $response, $args)
    {
        return ($this->view->render($response, 'test\index.twig', ['content'=>__CLASS__. ' - ' . __METHOD__]));
    }

    public function testAction(RequestInterface $request, ResponseInterface $response, $args)
    {
        return ($this->view->render($response, 'test\index.twig', ['content'=>__CLASS__. ' - ' . __METHOD__]));
    }

}