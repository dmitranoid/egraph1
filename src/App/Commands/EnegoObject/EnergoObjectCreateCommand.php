<?php
namespace App\Commands\EnergoObject;

use App\Commands\GenericCommand;
use Domain\Enums\ActivityStatus;
use Domain\Enums\EnergoObjectType;
use Domain\Enums\Voltage;

class EnergoObjectCreateCommand extends GenericCommand
{
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

    public function __construct($name, EnergoObjectType $type, Voltage $voltage, ActivityStatus $activityStatus)
    {
        $this->name = $name;
        $this->type = $type;
        $this->voltage = $voltage;
        $this->activityStatus = $activityStatus;
    }

    public function execute()
    {

    }
}