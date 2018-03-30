<?php

namespace App\Infrastructure\Repository;


use App\Infrastructure\Hydrator;

class PDORepositoryFactory
{
    /**
     *
     * @var \PDO $db
     */
    protected $db;

    public function __construct(\PDO $db){
        $this->db = $db;
    }

    public function getRepository($entityName){

        $fullClassname = "App\Infrastructure\Repository\PDO\\{$entityName}Repository";
        $hydrator = new Hydrator();        
        return new $fullClassname($this->db, $hydrator);
    }
}