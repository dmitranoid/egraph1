<?php
declare(strict_types=1);

namespace App\Actions\Admin\Energonetwork\Import;


use App\Actions\Action;
//use App\Actions\DomainRecordNotFoundException;
use App\Infrastructure\View\ViewInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\RouteParserInterface;

abstract class GenericImportAction extends Action
{
    public function __construct(LoggerInterface $logger, ViewInterface $view, RouteParserInterface $router, \PDO $pdo)
    {
        parent::__construct($logger, $view, $router, $pdo);
    }


}