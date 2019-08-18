<?php
namespace App\Commands\EnergoObject;

use Domain\Enums\ActivityStatus;
use Domain\Enums\EnergoObjectType;
use Domain\Enums\Voltage;

class CreateCommand
{
    /** @var string Наименование объекта */
    private $name;
    private $code;
    /** @var EnergoObjectType Тип (ПС, ТП, ...) */
    private $type;
    /** @var Voltage Категория напряжения объекта (750, 330, 220, 110, 10, 04) */
    private $voltage;
    /** @var ActivityStatus состояние обьекта (включено/отключено) */
    private $activityStatus;

    public function __construct($name, $code, EnergoObjectType $type, Voltage $voltage, ActivityStatus $activityStatus)
    {
        $this->name = $name;
        $this->code = $code;
        $this->type = $type;
        $this->voltage = $voltage;
        $this->activityStatus = $activityStatus;
    }
}