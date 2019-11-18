<?php
declare(strict_types=1);

namespace Test\App\Services\Import;

use App\Services\Import\DipolImportService;
use Monolog\Handler\PHPConsoleHandler;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

class DipolImportServiceTest extends TestCase
{

    /** @var PDO */
    protected $srcPdo;

    /** @var PDO */
    protected $dstPdo;


    protected function setUp(): void
    {
        parent::setUp();
        try {
            $db = realpath(__DIR__ . '..\..\..\..\..\data\data.sqlite3');
            $this->dstPdo = new PDO('sqlite:' . $db, '', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            echo ($e->getMessage());
        }
        $this->dstPdo->exec('PRAGMA journal_mode = MEMORY');

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

    /**
     * @doesNotPerformAssertions
     */
    public function testUpdateGeoCoords()
    {
        $logger = new TestLogger();
        $dipolImportService = new DipolImportService($this->srcPdo, $this->dstPdo, $logger);
        $dipolImportService->updateGeoCoords();

        var_dump($logger);

    }

}
