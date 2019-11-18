<?php
declare(strict_types=1);

namespace App\Actions\Admin\Energonetwork\Import;


use App\Actions\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class Index extends GenericImportAction
{
    protected function action(): Response
    {
        return $this->view->render($this->response, 'admin\network\import\index.twig', []);
    }

}