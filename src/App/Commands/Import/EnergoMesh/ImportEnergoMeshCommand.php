<?php


namespace App\Commands\Import\EnergoMesh;


use App\Commands\CommandInterface;

class ImportEnergoMeshCommand implements CommandInterface
{
    public \PDO $dstPdo;
    public \PDO $srcPdo;

    public function __construct(\PDO $srcPdo, \PDO $dstPdo)
    {
        $this->dstPdo = $dstPdo;
        $this->srcPdo = $srcPdo;
    }

}