<?php
declare(strict_types=1);

namespace App\Actions\Admin\Energonetwork\Import;


use App\Actions\DomainRecordNotFoundException;
use App\Commands\Import\EnergoMesh\ImportEnergoMeshCommand;
use App\Commands\Import\EnergoMesh\ImportEnergoMeshCommandHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class DoImport extends GenericImportAction
{
    protected function action(): Response
    {
        $formData = $this->request->getParsedBody();
            $commandHandler = new ImportEnergoMeshCommandHandler($this->logger);
            foreach ($formData['dbname'] as $dbname) {

                $srcHost = 'firebird:dbname=' . $resDbFile . ';charset=UTF8';
                $srcPdo = new PDO(
                    $srcHost,
                    'sysdba', 'masterkey',
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                $commandHandler->handle(new ImportEnergoMeshCommand($srcPdo, $this->pdo));
        }
        if (empty($srcFirebirdFile = $this->request->getQueryParams()['fdb_server_path'] ?? null)) {
            return $this->response->withRedirect($this->router->urlFor('adm.network.import.index'));
        }


    }

}