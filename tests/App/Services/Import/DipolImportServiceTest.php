<?php
declare(strict_types=1);

namespace Tests\App\Services\Import;

use App\Services\Import\DipolImportService;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;
use function Tests\Includes\initSqliteDb;

class DipolImportServiceTest extends TestCase
{

    /** @var PDO */
    protected $srcPdo;

    /** @var PDO */
    protected $dstPdo;

    /**
     * @doesNotPerformAssertions
     */
    public function testUpdateGeoCoords()
    {
        $logger = new TestLogger();
        $dipolImportService = new DipolImportService($this->srcPdo, $this->dstPdo, $logger);
        $dipolImportService->updateGeoCoords('111113');

        if ($logger->hasErrorRecords()) {
            var_dump($logger->recordsByLevel[LogLevel::ERROR]);
        }
        if ($logger->hasWarningRecords()) {
            var_dump($logger->recordsByLevel[LogLevel::WARNING]);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dstPdo = initSqliteDb();

        $tns = " 
(DESCRIPTION =
    (ADDRESS_LIST =
      (ADDRESS = (PROTOCOL = TCP)(HOST = servoracle1)(PORT = 1521))
    )
    (CONNECT_DATA =
      (SERVICE_NAME = orcl)
    )
  )";

        $this->srcPdo = new PDO('oci:dbname=' . $tns . ';charset=utf8', 'PASSPORT', '1');
    }

}
