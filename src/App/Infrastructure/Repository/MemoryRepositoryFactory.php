<?php

namespace App\Infrastructure\Repository;


use App\Infrastructure\Hydrator\Hydrator;

class MemoryRepositoryFactory
{
    /**
     *
     * @var \PDO $db
     */
    protected $db;

    public function __construct(){
    }

    public function getRepository($entityName){
        $fullClassname = "App\Infrastructure\Repository\Memory\{$entityName}Repository";
        $hydrator = new Hydrator();        
        return new $fullClassname( $hydrator);
    }
}