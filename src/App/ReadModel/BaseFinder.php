<?php

namespace App\Readmodel;

use \PDO;

class BaseFinder{

    /**
     * @var \PDO
     */
    protected $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

}