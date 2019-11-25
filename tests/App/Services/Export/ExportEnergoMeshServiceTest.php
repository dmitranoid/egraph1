<?php
declare(strict_types=1);

namespace Tests\App\Services\Export;


use App\Services\Export\ExportEnergoMeshService;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use Tests\App\Includes;

class ExportEnergoMeshServiceTest extends TestCase
{
    /** @var PDO */
    protected $pdo;

    protected function setUp():void
    {
        parent::setUp();
        $this->pdo = Includes\initSqliteDb();
    }

    public function testGetMeshFromDbForRegion()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $result = $service->getMeshFromDb('111113');
        $this->assertIsArray($result);
    }

    public function testGetMeshWithLevelHigh()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $result = $service->getMeshFromDb(null, ExportEnergoMeshService::NETWORK_LEVEL_HIGH);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testGetMeshWithWrongLevel()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $this->expectException(InvalidArgumentException::class);
        $result = $service->getMeshFromDb(null, 'wrongLevel');
    }

    public function testGetMeshWithLevelHighAndRegion()
    {
        $service =  new ExportEnergoMeshService($this->pdo);
        $result = $service->getMeshFromDb('111113', ExportEnergoMeshService::NETWORK_LEVEL_HIGH);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

}
