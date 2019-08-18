<?php

namespace Domain\Entities\EnergoConnection;

use Domain\Entities\EventTrait,
    Domain\Entities\EnergoObject\EnergoObject,
    Domain\Enums\Voltage,
    Domain\Enums\TransmissionDirection,
    Domain\Enums\ActivityStatus;

/**
 * Точка подключения к энергообъекту
 */
class EnergoConnection {
    use EventTrait;

    private $id;
    private $energoObject;
    private $name;
    private $code;
    private $voltage;
    private $direction;
    private $activityStatus;

    public function __construct($id, EnergoObject $energoObject, string $name, string $code, Voltage $voltage, TransmissionDirection $direction, ActivityStatus $activityStatus)
    {
        $this->id = $id;
        $this->energoObject = $energoObject;
        $this->name = $name;
        $this->code = $code;
        $this->direction = $direction;
        $this->voltage = $voltage;
    }
}
