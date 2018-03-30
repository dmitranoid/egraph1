<?php

namespace App\Infrastructure\Repository\Memory;


use App\Infractructure\Hydrator;

class BaseRepository
{

    /**
     * Entity hydrator
     *
     * @var Hydrator
     */
    protected $hydrator;

    /**
     * constructor
     *
     * @return void
     */
    public function __construct($hydrator) {
        $this->db = $db;
        $this->hydrator = $hydrator;
    }
}
