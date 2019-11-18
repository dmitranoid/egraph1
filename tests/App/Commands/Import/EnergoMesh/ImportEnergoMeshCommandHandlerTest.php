<?php

namespace Test\App\Commands\Import\EnergoMesh;


use App\Commands\Import\EnergoMesh\ImportEnergoMeshCommand;
use App\Commands\Import\EnergoMesh\ImportEnergoMeshCommandHandler;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ImportEnergoMeshCommandHandlerTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testHandle()
    {

        $srcHost = 'firebird:dbname=localhost:f:\wwwork\egraph_import_data\dwres2\iva.fdb;charset=UTF8';
        $srcPdo = new PDO(
            $srcHost,
            'sysdba', 'masterkey',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $dstPdo = new PDO('sqlite:..\..\..\..\..\data\data.sqlite3', '', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $dstPdo->exec('PRAGMA journal_mode = MEMORY');
        //$dstPdo = new \PDO('sqlite::memory:', '', '',  [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
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

        $commandHandler = new ImportEnergoMeshCommandHandler(new NullLogger());
        $commandHandler->handle(new ImportEnergoMeshCommand($srcPdo, $dstPdo));
    }
}
