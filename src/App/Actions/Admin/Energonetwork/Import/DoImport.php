<?php
declare(strict_types=1);

namespace App\Actions\Admin\Energonetwork\Import;


use App\Actions\DomainRecordNotFoundException;
use App\Commands\Import\EnergoMesh\ImportEnergoMeshCommand;
use App\Commands\Import\EnergoMesh\ImportEnergoMeshCommandHandler;
use App\Exceptions\ApplicationException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\Test\TestLogger;
use Slim\Exception\HttpBadRequestException;

class DoImport extends GenericImportAction
{
    protected function action(): Response
    {
        $formData = $this->request->getParsedBody();
        $importLogger = new TestLogger();  // TODO поменять на нормальный
        $commandHandler = new ImportEnergoMeshCommandHandler($this->logger);
        foreach ($formData['dbname'] as $dbname) {
            $resDbFile = ; // TODO load from config
            $srcHost = 'firebird:dbname=' . $resDbFile . ';charset=UTF8';
            $srcPdo = new PDO(
                $srcHost,
                'sysdba', 'masterkey',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            try {
                $commandHandler->handle(new ImportEnergoMeshCommand($srcPdo, $this->pdo));
            } catch (ApplicationException $e) {
                $importLogger->error($e->getMessage());
            }
        }
        return $this->view->render($this->response, 'admin/blank.twig', ['content' => $importLogger->records]);
//        return $this->response->withRedirect($this->router->urlFor('adm.network.import.index'));

    }

}