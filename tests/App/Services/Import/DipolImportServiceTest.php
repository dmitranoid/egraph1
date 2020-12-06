<?php
declare(strict_types=1);

namespace Tests\App\Services\Import;

use App\Services\Import\DipolImportService;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Psr\Log\Test\TestLogger;
use function Tests\App\Includes\initSqliteDb;

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
        $resCode = '111116';
        $dipolImportService = new DipolImportService($this->srcPdo, $this->dstPdo, $logger);
        $dipolImportService->updateGeoCoords($resCode);

        if ($logger->hasErrorRecords()) {
            var_dump($logger->recordsByLevel[LogLevel::ERROR]);
            foreach ($logger->recordsByLevel[LogLevel::ERROR] as $record) {
                file_put_contents(sprintf('d:/%s_%s.log', $resCode, LogLevel::ERROR), strtr($record['message'], $record['context']). PHP_EOL, FILE_APPEND);
            }
        }

        if ($logger->hasWarningRecords()) {
            file_put_contents(sprintf('d:/%s_%s.log', $resCode, LogLevel::WARNING), '');
            var_dump($logger->recordsByLevel[LogLevel::WARNING]);
            foreach ($logger->recordsByLevel[LogLevel::WARNING] as $record) {
                file_put_contents(sprintf('d:/%s_%s.log', $resCode, LogLevel::WARNING), strtr($record['message'], $record['context']). PHP_EOL, FILE_APPEND);
            }
        }
    }

    private function array_flatten($array, $prefix = '')
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + $this->array_flatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
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
      (SID = orcl)
    )
  )";

        $this->srcPdo = new PDO('oci:dbname=' . $tns . ';charset=utf8', 'PASSPORT', '1');
    }

}
