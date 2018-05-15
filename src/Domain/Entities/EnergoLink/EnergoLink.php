<?php

namespace Domain\Entities\EnergoLink;

use Domain\Entities\EventTrait,
    Domain\Entities\EnergoConnection\EnergoConnection,
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