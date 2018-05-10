<?php

namespace App\Infrastructure\Repository\Memory;


use App\Infractructure\Hydrator\HydratorInterface;

class BaseMemoryRepository
{
    /**
     * Entity hydrator
     *
     * @var HydratorInterface;
    protected $hydrator;

    /**
     * constructor
     * @var HydratorInterface;
     */
    public function __construct($hydrator) {
        $this->hydrator = $hydrator;
    }
}
