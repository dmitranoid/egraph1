<?php

namespace App\Commands\Import\EnergoMesh;


use Monolog\Handler\PHPConsoleHandler;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;

class ImportEnergoMeshCommandHandlerTest extends TestCase
{
    public function testHandle()
    {

        $srcHost = 'firebird:dbname=localhost:f:\wwwork\egraph_import_data\dwres2\gan.FDB;charset=UTF8'; // charset=WIN1251
        $srcPdo = new \PDO(
            $srcHost,
            'sysdba', 'masterkey',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
        $dstPdo = new \PDO('sqlite:F:\wwwork\egraph1\data\data.sqlite3', '', '',  [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $dstPdo->exec('PRAGMA journal_mode = MEMORY');
        //$dstPdo = new \PDO('sqlite::memory:', '', '',  [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $dstPdo->exec('
            CREATE TABLE IF NOT EXISTS energoObject (
                id INTEGER PRIMARY KEY, 
                id_res TEXT,
                code TEXT, 
                name TEXT, 
                type TEXT, 
                voltage TEXT, 
                status BOOL
                )');
        $dstPdo->exec('
            CREATE TABLE IF NOT EXISTS energoConnection (
                id INTEGER PRIMARY KEY, 
                id_energoObject TEXT,
                code TEXT, 
                name TEXT, 
                voltage TEXT, 
                direction TEXT,
                status BOOL
                )');

        $dstPdo->exec('
            CREATE TABLE IF NOT EXISTS energoLink (
                id INTEGER PRIMARY KEY, 
                id_srcConnection TEXT,
                id_dstConnection TEXT,
                code TEXT, 
                name TEXT, 
                status BOOL
                )');

        $commandHandler = new ImportEnergoMeshCommandHandler(new NullLogger());
        $this->assertNull($commandHandler->handle(new ImportEnergoMeshCommand($srcPdo, $dstPdo)));
    }
}
