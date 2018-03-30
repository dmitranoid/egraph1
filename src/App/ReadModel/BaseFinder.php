<?php

namespace App\Readmodel;

class BaseFinder{

    /**
     * @var \PDO
     */
    protected $db;

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

}