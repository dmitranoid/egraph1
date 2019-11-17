<?php
declare(strict_types=1);

namespace Test\App\Services\Export;


use App\Services\Export\ExportEnergoMeshService;
use PDO;
use PHPUnit\Framework\TestCase;

class ExportEnergoMeshServiceTest extends TestCase
{
    /** @var PDO */
    protected $pdo;

    protected function setUp():void
    {
        parent::setUp();
        $this->pdo = new PDO('sqlite:..\..\..\..\data\data.sqlite3', '', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->pdo->exec('PRAGMA journal_mode = MEMORY');
    }

    public function testExportCytoscapeJson(): string
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $result = $service->exportCytoscapeJson();
        $this->assertIsArray($result);
    }

}
