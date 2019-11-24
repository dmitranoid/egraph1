<?php

namespace Tests\App\Services\Export;

use App\Services\Export\ExportEnergoTree;
use PDO;
use PHPUnit\Framework\TestCase;
use function Tests\Includes\initSqliteDb;

class ExportEnergoTreeTest extends TestCase
{
    /** @var PDO $pdo */
    private $pdo;

    /**
     * @doesNotPerformAssertions
     */
    public function testExportFull()
    {
        $exportTreeService = new ExportEnergoTree($this->pdo);
        $exportTreeService->export();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = initSqliteDb();
    }

}
