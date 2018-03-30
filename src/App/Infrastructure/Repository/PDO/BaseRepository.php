<?php

namespace App\Infrastructure\Repository\PDO;


use App\Infractructure\Hydrator;

class BaseRepository
{
    /**
     * db handle
     *
     * @var \PDO
     */
    protected $db;

    /**
     * Entity hydrator
     *
     * @var Hydrator
     */
    protected $hydrator;

    /**
     * constructor
     *
     * @param \PDO $db
     * @return void
     */
    public function __construct(\PDO $db, $hydrator) {
        $this->db = $db;
        $this->hydrator = $hydrator;
    }

    public function fieldsToParams(array $fields):array {
        $result = [];
        foreach ($fields as $field => $value) {
            $result[':'.$field] = $value;
        }
        return $result;
    }
}
