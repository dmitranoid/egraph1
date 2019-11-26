<?php
declare(strict_types=1);

namespace Tests\App\Services\Import;

use App\Services\Import\OduDictImportService;
use PHPUnit\Framework\TestCase;
use function Tests\App\Includes\initSqliteDb;

class OduDictImportServiceTest extends TestCase
{

    /** @doesNotPerformAssertions */
    public function testImportSubstations()
    {
        $pdo = initSqliteDb();
        $oduImportService = new OduDictImportService($pdo, $pdo);
        $oduImportService->importSubstations();
    }

    /** @doesNotPerformAssertions */
    public function testImportLinks()
    {
        $pdo = initSqliteDb();
        $oduImportService = new OduDictImportService($pdo, $pdo);
        $oduImportService->importLinks();
    }
}
