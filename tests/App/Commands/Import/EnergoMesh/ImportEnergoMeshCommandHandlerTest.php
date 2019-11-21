<?php

namespace Test\App\Commands\Import\EnergoMesh;


use App\Commands\Import\EnergoMesh\ImportEnergoMeshCommand;
use App\Commands\Import\EnergoMesh\ImportEnergoMeshCommandHandler;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;

class ImportEnergoMeshCommandHandlerTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testHandle()
    {
        $dwresFiles = [
            'localhost:f:\wwwork\egraph_import_data\dwres2\lah.fdb',
            'localhost:f:\wwwork\egraph_import_data\dwres2\iva.fdb',
            'localhost:f:\wwwork\egraph_import_data\dwres2\ber.fdb',
            'localhost:f:\wwwork\egraph_import_data\dwres2\gan.fdb',
            'localhost:f:\wwwork\egraph_import_data\dwres2\barg.fdb',
            'localhost:f:\wwwork\egraph_import_data\dwres2\bars.fdb',
        ];

        $sqlitePath = '../../../../../data/';
        $dstPdo = new PDO('sqlite:'. $sqlitePath .'data.sqlite3', '', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $dstPdo->exec('ATTACH  \'' . $sqlitePath . 'dict-gpo.sqlite3\' as gpo');
        $dstPdo->exec('PRAGMA journal_mode = MEMORY');

        /*
                $dstPdo->exec('
                CREATE TABLE IF NOT EXISTS region
                (
                    id integer not null constraint region_pk primary key autoincrement,
                    code text not null,
                    name text
                )');

                $dstPdo->exec('
                    CREATE TABLE IF NOT EXISTS energoObject (
                        id INTEGER PRIMARY KEY,
                        code_region TEXT,
                        code TEXT not null,
                        name TEXT,
                        type TEXT,
                        voltage TEXT,
                        status BOOL
                        )');
                $dstPdo->exec('
                    CREATE TABLE IF NOT EXISTS energoConnection (
                        id INTEGER PRIMARY KEY,
                        code_energoObject TEXT,
                        code TEXT,
                        name TEXT,
                        voltage TEXT,
                        direction TEXT,
                        status BOOL
                        )');

                $dstPdo->exec('
                    CREATE TABLE IF NOT EXISTS energoLink (
                        id INTEGER PRIMARY KEY,
                        code_srcConnection TEXT,
                        code_dstConnection TEXT,
                        code_region TEXT,
                        code TEXT,
                        name TEXT,
                        status BOOL
                        )');
        */
        $logger = new TestLogger();
        $commandHandler = new ImportEnergoMeshCommandHandler($logger);
        foreach ($dwresFiles as $resDbFile) {
            $srcHost = 'firebird:dbname=' . $resDbFile . ';charset=UTF8';
            $srcPdo = new PDO(
                $srcHost,
                'sysdba', 'masterkey',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $commandHandler->handle(new ImportEnergoMeshCommand($srcPdo, $dstPdo));
            var_dump($logger->recordsByLevel['error']);
        }
    }
}
