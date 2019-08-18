<?php

namespace  Domain\Entities\EnergoObject;

use Domain\Entities\EventTrait,
    Domain\Enums\ActivityStatus,
    Domain\Enums\Voltage,
    Domain\Enums\EnergoObjectType;

/**
 * Обьект энергосети
 *
 * Class EnergoObject
 * @package Domain\Entities\EnergoObject
 */
class EnergoObject {
    use EventTrait;

    /** @var string Код обьекта */
    private $code;

    /** @var string Наименование объекта */
    private $name;

    /** @var EnergoObjectType Тип (ПС, ТП, ...) */
    private $type;

    /** @var Voltage Категория напряжения объекта (750, 330, 220, 110, 10, 04) */
    private $voltage;

    private $activityStatus;

    public function __construct($code, $name, EnergoObjectType $type, Voltage $voltage, ActivityStatus $activityStatus) {
        $this->code = $code;
        $this->name = $name;
        $this->type = $type;
        $this->voltage = $voltage;
        $this->activityStatus = $activityStatus;
    }
}