<?php

namespace Test\App\Services\Export;

use App\Services\Export\ExportEnergoTree;
use PDO;
use PHPUnit\Framework\TestCase;

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
        $sqlitePath = '../../../../data/';
//        echo(realpath(__DIR__ . '/'. $sqlitePath));
        $pdo = new PDO(
            'sqlite:' . $sqlitePath . 'data.sqlite3',
            '',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $pdo->exec('ATTACH  \'' . $sqlitePath . 'dict-gpo.sqlite3\' as gpo');
        $pdo->exec('PRAGMA journal_mode = MEMORY');

        $this->pdo = $pdo;
//        $this->pdo = initSqliteDb();
    }

}
