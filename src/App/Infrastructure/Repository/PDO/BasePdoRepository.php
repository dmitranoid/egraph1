<?php

namespace App\Infrastructure\Repository\PDO;

use App\Infractructure\Hydrator\HydratorInterface;
use PDO;

class BasePdoRepository
{
    /**
     * db handle
     *
     * @var PDO
     */
    protected $db;

    /**
     * Entity hydrator
     *
     * @var HydratorInterface
     */
    protected $hydrator;

    /**
     * constructor
     *
     * @param PDO $db
     * @param HydratorInterface $hydrator
     * @return void
     */
    public function __construct(PDO $db, $hydrator)
    {
        $this->db = $db;
        $this->hydrator = $hydrator;
    }

    public function fieldsToParams(array $fields): array
    {
        $result = [];
        foreach ($fields as $field => $value) {
            $result[':'.$field] = $value;
        }
        return $result;
    }
}
