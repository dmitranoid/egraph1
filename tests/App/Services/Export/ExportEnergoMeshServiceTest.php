<?php
declare(strict_types=1);

namespace Tests\App\Services\Export;


use App\Services\Export\ExportEnergoMeshService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\Includes;

class ExportEnergoMeshServiceTest extends TestCase
{
    /** @var PDO */
    protected $pdo;

    protected function setUp():void
    {
        parent::setUp();
        $dbFile = __DIR__ . '..\..\..\..\..\data\data.sqlite3';
        $this->pdo = Includes\initSqliteDb();
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
        $this->expectException(InvalidArgumentException::class);
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
