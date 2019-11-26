<?php
declare(strict_types=1);

namespace Tests\App\Services\Import;

use App\Services\Import\DwresImportService;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use function Tests\App\Includes\initSqliteDb;

class DwresImportServiceTest extends TestCase
{
    private $srcPdo;
    private $dstPdo;

    protected function setUp():void
    {
        parent::setUp();

        $srcHost = 'firebird:dbname=localhost:f:\wwwork\egraph_import_data\dwres2\bars.fdb;charset=UTF8';
        $this->srcPdo = new PDO(
            $srcHost,
            'sysdba', 'masterkey',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $this->dstPdo = initSqliteDb();
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testImport()
    {
        $logger = new TestLogger();
        $dwresImportService = new DwresImportService($this->srcPdo, $this->dstPdo, $logger);
        $dwresImportService->import();
        var_dump($logger);
    }
}
