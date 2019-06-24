<?php

namespace  Domain\Entities\EnergoObject;

use Domain\Entities\EventTrait,
    Domain\Enums\ActivityStatus,
    Domain\Enums\Voltage,
    Domain\Enums\EnergoObjectType;

class EnergoObject {
    use EventTrait;

    /**
     * Наименование объекта
     *
     * @var string
     */
    private $name;

    /**
     * Тип (ПС, ТП, ...)
     *
     * @var EnergoObjectType
     */
    private $type;

    /**
     * Категория напряжения объекта (750, 330, 220, 110, 10, 04)
     *
     * @var Voltage
     */
    private $voltage;

    private $activityStatus;

    public function __construct($name, EnergoObjectType $type, Voltage $voltage, ActivityStatus $activityStatus) {
        $this->name = $name;
        $this->type = $type;
        $this->voltage = $voltage;
        $this->activityStatus = $activityStatus;
    }
}