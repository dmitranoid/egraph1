<?php


namespace App\Commands\Import\EnergoMesh;


use App\Commands\CommandInterface;

class ImportEnergoMeshCommand implements CommandInterface
{
    /**
     * @var \PDO
     */
    public $dstPdo;
    /**
     * @var \PDO
     */
    public $srcPdo;

    public function __construct(\PDO $srcPdo, \PDO $dstPdo)
    {
        $this->dstPdo = $dstPdo;
        $this->srcPdo = $srcPdo;
    }

}