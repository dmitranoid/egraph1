<?php

namespace  Domain\Entities\EnergoObject;

use Domain\Entities\EventTrait,
Domain\Entites\EnergoConnection\EnergoConnection,
Domain\Exceptions\DomainException;

/**
 * Связь между двумя точками подключения сети
 */
class EnergoLink {
    use EventTrait;

    private $srcConnection;
    private $dstConnection;
    private $name;
    private $status;

    public function __construct(EnergoConnection $srcConnection, EnergoConnection $dstConnection, string $name, $status) {
        $this->srcConnection = $srcConnection;
        $this->dstConnection = $dstConnection;
    }
}