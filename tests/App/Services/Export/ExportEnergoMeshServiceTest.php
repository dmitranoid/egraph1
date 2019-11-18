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

    public function testGetMeshRegion()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $result = $service->getMesh('51400');
        $this->assertIsArray($result);
    }

    public function testGetMeshLevel()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $result = $service->getMesh(null, ExportEnergoMeshService::NETWORK_LEVEL_HIGH);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testGetMeshWrongLevel()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $this->expectException(\InvalidArgumentException::class);
        $result = $service->getMesh(null, 'abs');
    }

}
