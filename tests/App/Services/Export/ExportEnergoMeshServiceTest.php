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
        $dbFile = __DIR__ . '..\..\..\..\..\data\data.sqlite3';
        $this->pdo = new PDO('sqlite:' . $dbFile, '', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->pdo->exec('PRAGMA journal_mode = MEMORY');
    }

    public function testGetMeshForRegion()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $result = $service->getMesh('111113');
        $this->assertIsArray($result);
    }

    public function testGetMeshWithLevelHigh()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $result = $service->getMesh(null, ExportEnergoMeshService::NETWORK_LEVEL_HIGH);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testGetMeshWithWrongLevel()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $this->expectException(\InvalidArgumentException::class);
        $result = $service->getMesh(null, 'wrongLevel');
    }

    public function testGetMeshWithLevelHighAndRegion()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $result = $service->getMesh('111113', ExportEnergoMeshService::NETWORK_LEVEL_HIGH);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

}
