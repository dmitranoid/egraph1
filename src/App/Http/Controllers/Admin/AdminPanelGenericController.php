<?php
declare(strict_types=1);


namespace App\Http\Controllers\Admin;


use App\Infrastructure\View\ViewInterface;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;

abstract class AdminPanelGenericController
{
    protected ServerRequestInterface $request;
    protected ResponseInterface $response;
    protected $args;
    protected LoggerInterface $logger;
    protected PDO $pdo;
    protected ViewInterface $view;

    public function __construct(LoggerInterface $logger, ViewInterface $view, PDO $pdo)
    {
        $this->logger = $logger;
        $this->view = $view;
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        return $this->action();
    }

    abstract protected function action();
}