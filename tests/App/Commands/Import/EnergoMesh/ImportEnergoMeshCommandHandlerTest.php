<?php

namespace App\Commands\Import\EnergoMesh;


use PHPUnit\Framework\TestCase;
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
        //$dstPdo = new \PDO('sqlite::memory:', '', '',  [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $dstPdo->exec('
            CREATE TABLE IF NOT EXISTS energoObject (
                id INTEGER PRIMARY KEY, 
                id_res TEXT,
                code TEXT, 
                name TEXT, 
                type TEXT, 
                voltage TEXT, 
                activity BOOL
                )');
        $dstPdo->exec('
            CREATE TABLE IF NOT EXISTS energoConnection (
                id INTEGER PRIMARY KEY, 
                id_energoObject TEXT,
                code TEXT, 
                name TEXT, 
                voltage TEXT, 
                direction TEXT,
                activity BOOL
                )');

        $dstPdo->exec('
            CREATE TABLE IF NOT EXISTS energoLink (
                id INTEGER PRIMARY KEY, 
                id_srcConnection TEXT,
                id_dstConnection TEXT,
                code TEXT, 
                name TEXT, 
                activity BOOL
                )');

        $commandHandler = new ImportEnergoMeshCommandHandler(new TestLogger());
        $result = $commandHandler->handle(new ImportEnergoMeshCommand($srcPdo, $dstPdo));
        $this->assertTrue($result);
    }
}
