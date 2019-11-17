<?php
declare(strict_types=1);


namespace App\Api\Controllers\EnergoMesh;


use App\Services\Export\ExportEnergoMeshService;
use PDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class EnergoMeshController

{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * EnergoMeshController constructor.
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $exportService = new ExportEnergoMeshService($this->pdo);
        $data = $exportService->exportCytoscapeJson();
        return $response->withJson($data);
    }
}