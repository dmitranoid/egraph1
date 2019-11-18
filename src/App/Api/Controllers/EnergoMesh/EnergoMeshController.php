<?php
declare(strict_types=1);


namespace App\Api\Controllers\EnergoMesh;


use App\Services\Export\ExportEnergoMeshService;
use PDO;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    public function all(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $query = $request->getQueryParams();
        $region = $query['region'] ?? null;
        $exportService = new ExportEnergoMeshService($this->pdo);
        $data = $exportService->exportCytoscapeJson($region);
        return $response->withJson($data);
    }

    public function highNetworks(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $query = $request->getQueryParams();
        $region = $query['region'] ?? null;
        $exportService = new ExportEnergoMeshService($this->pdo);
        $data = $exportService->exportCytoscapeJson($region, ExportEnergoMeshService::NETWORK_LEVEL_HIGH);
        return $response->withJson($data);

    }

    public function lowNetworks(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $query = $request->getQueryParams();
        $region = $query['region'] ?? null;
        $exportService = new ExportEnergoMeshService($this->pdo);
        $data = $exportService->exportCytoscapeJson($region, ExportEnergoMeshService::NETWORK_LEVEL_LOW);
        return $response->withJson($data);

    }


}